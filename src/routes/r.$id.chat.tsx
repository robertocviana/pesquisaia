import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useSurvey } from "@/hooks/use-surveys";
import { useEffect, useRef, useState } from "react";
import { Send, Sparkles } from "lucide-react";

export const Route = createFileRoute("/r/$id/chat")({
  head: () => ({ meta: [{ title: "Pesquisa" }] }),
  component: RespondentChat,
});

function RespondentChat() {
  const { id } = Route.useParams();
  const survey = useSurvey(id);
  const navigate = useNavigate();
  const [step, setStep] = useState(0);
  const [messages, setMessages] = useState<{ role: "assistant" | "user"; text: string }[]>([]);
  const [input, setInput] = useState("");
  const endRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (survey && messages.length === 0 && survey.questions.length > 0) {
      setMessages([
        { role: "assistant", text: "Oi! Obrigada por participar 😊" },
        { role: "assistant", text: survey.questions[0].text },
      ]);
    }
  }, [survey, messages.length]);

  useEffect(() => { endRef.current?.scrollIntoView({ behavior: "smooth" }); }, [messages]);

  if (!survey) return <div className="p-10">Pesquisa não encontrada.</div>;

  const total = survey.questions.length;
  const progress = total > 0 ? Math.min(100, Math.round((step / total) * 100)) : 0;

  function send() {
    const text = input.trim();
    if (!text || !survey) return;
    setInput("");
    const next = [...messages, { role: "user" as const, text }];
    setMessages(next);

    setTimeout(() => {
      const nextStep = step + 1;
      if (nextStep >= total) {
        navigate({ to: "/r/$id/concluido", params: { id } });
        return;
      }
      setMessages([...next, { role: "assistant", text: survey.questions[nextStep].text }]);
      setStep(nextStep);
    }, 500);
  }

  return (
    <div className="min-h-screen flex flex-col bg-muted/30">
      <header className="bg-card border-b border-border px-5 py-3 flex items-center gap-3">
        <div className="w-9 h-9 rounded-full bg-primary flex items-center justify-center">
          <Sparkles className="w-4 h-4 text-primary-foreground" />
        </div>
        <div className="flex-1 min-w-0">
          <div className="font-semibold text-sm truncate">{survey.name}</div>
          <div className="text-xs text-muted-foreground">Pergunta {Math.min(step + 1, total)} de {total}</div>
        </div>
      </header>
      <div className="h-1 bg-border">
        <div className="h-full bg-primary transition-all" style={{ width: `${progress}%` }} />
      </div>

      <div className="flex-1 overflow-y-auto p-4">
        <div className="max-w-xl mx-auto space-y-3">
          {messages.map((m, i) => (
            m.role === "assistant" ? (
              <div key={i} className="flex justify-start">
                <div className="max-w-[80%] rounded-2xl rounded-tl-sm bg-card border border-border px-4 py-2.5 text-sm shadow-soft">{m.text}</div>
              </div>
            ) : (
              <div key={i} className="flex justify-end">
                <div className="max-w-[80%] rounded-2xl rounded-tr-sm bg-primary px-4 py-2.5 text-sm text-primary-foreground">{m.text}</div>
              </div>
            )
          ))}
          <div ref={endRef} />
        </div>
      </div>

      <div className="border-t border-border bg-card p-3">
        <div className="max-w-xl mx-auto flex items-end gap-2">
          <textarea
            value={input}
            onChange={(e) => setInput(e.target.value)}
            onKeyDown={(e) => { if (e.key === "Enter" && !e.shiftKey) { e.preventDefault(); send(); } }}
            rows={1}
            placeholder="Digite sua resposta..."
            className="flex-1 resize-none rounded-full border border-input bg-card px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
          />
          <button onClick={send} className="rounded-full bg-primary p-3 text-primary-foreground hover:opacity-90 transition">
            <Send className="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>
  );
}
