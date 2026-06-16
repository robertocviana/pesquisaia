import { createFileRoute, Link, useNavigate } from "@tanstack/react-router";
import { AppShell, PageHeader } from "@/components/app-shell";
import { useSurvey } from "@/hooks/use-surveys";
import { upsertSurvey } from "@/lib/mock-data";
import { Pencil, Trash2, GripVertical, Plus, ArrowLeft, Rocket } from "lucide-react";
import { useState } from "react";

export const Route = createFileRoute("/pesquisas/$id/revisao")({
  head: () => ({ meta: [{ title: "Revisão — PesquisaIA" }] }),
  component: ReviewPage,
});

function ReviewPage() {
  const { id } = Route.useParams();
  const navigate = useNavigate();
  const survey = useSurvey(id);
  const [editing, setEditing] = useState<string | null>(null);

  if (!survey) return <AppShell><div className="p-10">Pesquisa não encontrada.</div></AppShell>;

  function update(patch: Partial<typeof survey>) {
    if (!survey) return;
    upsertSurvey({ ...survey, ...patch });
  }

  function publish() {
    update({ status: "ativa" });
    navigate({ to: "/pesquisas/$id", params: { id } });
  }

  return (
    <AppShell>
      <div className="max-w-3xl mx-auto p-6 sm:p-10">
        <Link to="/pesquisas/nova" className="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground mb-4">
          <ArrowLeft className="w-4 h-4" /> Voltar ao chat
        </Link>
        <PageHeader title="Revisão da pesquisa" subtitle="Ajuste as informações antes de publicar." />

        <section className="rounded-xl border border-border bg-card p-6 shadow-soft mb-6">
          <h2 className="font-semibold mb-4">Dados gerais</h2>
          <div className="space-y-4">
            <Field label="Nome da pesquisa" value={survey.name} onChange={(v) => update({ name: v })} />
            <Field label="Objetivo" value={survey.objective} onChange={(v) => update({ objective: v })} multiline />
            <Field label="Público-alvo" value={survey.audience} onChange={(v) => update({ audience: v })} />
          </div>
        </section>

        <section className="rounded-xl border border-border bg-card p-6 shadow-soft mb-6">
          <div className="flex items-center justify-between mb-4">
            <h2 className="font-semibold">Perguntas ({survey.questions.length})</h2>
            <button
              onClick={() => update({ questions: [...survey.questions, { id: "q" + Date.now(), text: "Nova pergunta" }] })}
              className="inline-flex items-center gap-1.5 rounded-lg border border-input bg-card px-3 py-1.5 text-sm hover:bg-muted transition"
            >
              <Plus className="w-3.5 h-3.5" /> Adicionar pergunta
            </button>
          </div>
          <ul className="space-y-2">
            {survey.questions.map((q, i) => (
              <li key={q.id} className="flex items-start gap-2 rounded-lg border border-border p-3 group">
                <GripVertical className="w-4 h-4 text-muted-foreground mt-0.5 cursor-grab" />
                <span className="text-xs text-muted-foreground mt-0.5 w-6">{i + 1}.</span>
                {editing === q.id ? (
                  <input
                    autoFocus
                    defaultValue={q.text}
                    onBlur={(e) => {
                      const next = survey.questions.map((qq) => qq.id === q.id ? { ...qq, text: e.target.value } : qq);
                      update({ questions: next });
                      setEditing(null);
                    }}
                    className="flex-1 bg-transparent text-sm focus:outline-none"
                  />
                ) : (
                  <span className="flex-1 text-sm">{q.text}</span>
                )}
                <button onClick={() => setEditing(q.id)} className="p-1 rounded hover:bg-muted opacity-0 group-hover:opacity-100 transition">
                  <Pencil className="w-3.5 h-3.5" />
                </button>
                <button
                  onClick={() => update({ questions: survey.questions.filter((qq) => qq.id !== q.id) })}
                  className="p-1 rounded hover:bg-muted opacity-0 group-hover:opacity-100 transition"
                >
                  <Trash2 className="w-3.5 h-3.5 text-destructive" />
                </button>
              </li>
            ))}
          </ul>
        </section>

        <div className="flex justify-end gap-2">
          <Link to="/pesquisas" className="rounded-lg border border-input bg-card px-4 py-2.5 text-sm hover:bg-muted transition">Voltar</Link>
          <button onClick={publish} className="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground shadow-pop hover:opacity-90 transition">
            <Rocket className="w-4 h-4" /> Publicar pesquisa
          </button>
        </div>
      </div>
    </AppShell>
  );
}

function Field({ label, value, onChange, multiline }: { label: string; value: string; onChange: (v: string) => void; multiline?: boolean }) {
  return (
    <div>
      <label className="text-sm font-medium">{label}</label>
      {multiline ? (
        <textarea
          value={value}
          onChange={(e) => onChange(e.target.value)}
          rows={2}
          className="mt-1.5 w-full rounded-lg border border-input bg-card px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
        />
      ) : (
        <input
          value={value}
          onChange={(e) => onChange(e.target.value)}
          className="mt-1.5 w-full rounded-lg border border-input bg-card px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
        />
      )}
    </div>
  );
}
