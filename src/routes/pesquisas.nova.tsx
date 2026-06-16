import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { AppShell } from "@/components/app-shell";
import { createDraft, upsertSurvey, type Survey } from "@/lib/mock-data";
import { useEffect, useRef, useState } from "react";
import { Send, Sparkles, CheckCircle2 } from "lucide-react";

export const Route = createFileRoute("/pesquisas/nova")({
  head: () => ({ meta: [{ title: "Nova pesquisa — PesquisaIA" }] }),
  component: NewSurvey,
});

const steps = [
  { key: "objetivo", label: "Objetivo" },
  { key: "publico", label: "Público-alvo" },
  { key: "perguntas", label: "Perguntas" },
  { key: "revisao", label: "Revisão" },
] as const;

const aiFlow: { ask: string; field: keyof Survey | null }[] = [
  { ask: "Perfeito! Qual público vai responder essa pesquisa?", field: "objective" },
  { ask: "Excelente. Quantas perguntas você gostaria? Posso sugerir 4 perguntas baseadas no seu objetivo.", field: "audience" },
  { ask: "Pronto! Gerei 4 perguntas para você. Vamos para a revisão para você ajustar antes de publicar.", field: null },
];

function NewSurvey() {
  const navigate = useNavigate();
  const [survey] = useState<Survey>(() => createDraft());
  const [messages, setMessages] = useState(survey.chat);
  const [step, setStep] = useState(0);
  const [input, setInput] = useState("");
  const endRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    endRef.current?.scrollIntoView({ behavior: "smooth" });
  }, [messages]);

  function send() {
    const text = input.trim();
    if (!text) return;
    const next = [...messages, { role: "user" as const, text }];
    setInput("");

    // apply field
    const flow = aiFlow[step];
    const draft = { ...survey, chat: next };
    if (flow?.field === "objective") draft.objective = text;
    if (flow?.field === "audience") draft.audience = text;

    setMessages(next);

    setTimeout(() => {
      const newStep = step + 1;
      if (newStep >= aiFlow.length) {
        // finish: generate questions and navigate to review
        draft.name = draft.objective.split(" ").slice(0, 4).join(" ") || "Nova pesquisa";
        draft.questions = [
          { id: "q1", text: "Como você descreveria sua experiência geral?" },
          { id: "q2", text: "O que mais te agradou?" },
          { id: "q3", text: "O que poderíamos melhorar?" },
          { id: "q4", text: "Você nos recomendaria a um amigo? Por quê?" },
        ];
        draft.chat = [...next, { role: "assistant", text: aiFlow[step].ask }];
        upsertSurvey(draft);
        navigate({ to: "/pesquisas/$id/revisao", params: { id: draft.id } });
        return;
      }
      const updated = [...next, { role: "assistant" as const, text: aiFlow[step].ask }];
      draft.chat = updated;
      upsertSurvey(draft);
      setMessages(updated);
      setStep(newStep);
    }, 600);
  }

  const progress = Math.min(100, Math.round(((step) / aiFlow.length) * 100));

  return (
    <AppShell>
      <div className="grid lg:grid-cols-[1fr_320px] h-screen">
        <div className="flex flex-col min-h-0">
          <div className="border-b border-border px-6 py-4 flex items-center gap-3">
            <div className="w-9 h-9 rounded-lg bg-primary flex items-center justify-center">
              <Sparkles className="w-4 h-4 text-primary-foreground" />
            </div>
            <div>
              <div className="font-semibold">Assistente de pesquisa</div>
              <div className="text-xs text-muted-foreground">Vamos criar sua pesquisa juntos.</div>
            </div>
          </div>

          <div className="flex-1 overflow-y-auto px-6 py-8">
            <div className="max-w-2xl mx-auto space-y-5">
              {messages.map((m, i) => (
                <Message key={i} role={m.role} text={m.text} />
              ))}
              <div ref={endRef} />
            </div>
          </div>

          <div className="border-t border-border p-4">
            <div className="max-w-2xl mx-auto flex items-end gap-2">
              <textarea
                value={input}
                onChange={(e) => setInput(e.target.value)}
                onKeyDown={(e) => {
                  if (e.key === "Enter" && !e.shiftKey) { e.preventDefault(); send(); }
                }}
                rows={1}
                placeholder="Escreva sua resposta..."
                className="flex-1 resize-none rounded-lg border border-input bg-card px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
              />
              <button onClick={send} className="rounded-lg bg-primary p-2.5 text-primary-foreground hover:opacity-90 transition">
                <Send className="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>

        <aside className="hidden lg:block border-l border-border bg-sidebar p-6">
          <div className="text-xs uppercase tracking-wide text-muted-foreground mb-2">Pesquisa</div>
          <div className="font-semibold mb-6">{survey.name}</div>

          <div className="mb-6">
            <div className="flex items-center justify-between text-sm mb-2">
              <span className="font-medium">Progresso</span>
              <span className="text-muted-foreground">{progress}%</span>
            </div>
            <div className="h-2 rounded-full bg-muted overflow-hidden">
              <div className="h-full bg-primary transition-all" style={{ width: `${progress}%` }} />
            </div>
          </div>

          <div className="space-y-2">
            {steps.map((s, i) => {
              const done = i < step;
              const current = i === step;
              return (
                <div key={s.key} className={`flex items-center gap-2.5 text-sm ${done ? "text-success" : current ? "text-foreground font-medium" : "text-muted-foreground"}`}>
                  {done ? <CheckCircle2 className="w-4 h-4" /> : <span className={`w-4 h-4 rounded-full border-2 ${current ? "border-primary" : "border-muted-foreground/40"}`} />}
                  {s.label}
                </div>
              );
            })}
          </div>
        </aside>
      </div>
    </AppShell>
  );
}

function Message({ role, text }: { role: "assistant" | "user"; text: string }) {
  if (role === "assistant") {
    return (
      <div className="flex gap-3">
        <div className="w-8 h-8 shrink-0 rounded-lg bg-primary flex items-center justify-center">
          <Sparkles className="w-4 h-4 text-primary-foreground" />
        </div>
        <div className="text-sm leading-relaxed text-foreground pt-1">{text}</div>
      </div>
    );
  }
  return (
    <div className="flex justify-end">
      <div className="max-w-[80%] rounded-2xl rounded-tr-sm bg-primary px-4 py-2.5 text-sm text-primary-foreground">{text}</div>
    </div>
  );
}
