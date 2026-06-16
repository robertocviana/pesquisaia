import { createFileRoute, Link } from "@tanstack/react-router";
import { AppShell, PageHeader } from "@/components/app-shell";
import { useSurvey } from "@/hooks/use-surveys";
import { formatDate } from "@/lib/mock-data";
import { ArrowLeft, Clock, User, Calendar } from "lucide-react";

export const Route = createFileRoute("/pesquisas/$id/respostas/$rid")({
  head: () => ({ meta: [{ title: "Detalhe da resposta — PesquisaIA" }] }),
  component: ResponseDetail,
});

function ResponseDetail() {
  const { id, rid } = Route.useParams();
  const survey = useSurvey(id);
  const response = survey?.responses.find((r) => r.id === rid);

  if (!survey || !response) return <AppShell><div className="p-10">Resposta não encontrada.</div></AppShell>;

  return (
    <AppShell>
      <div className="max-w-3xl mx-auto p-6 sm:p-10">
        <Link to="/pesquisas/$id/respostas" params={{ id }} className="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground mb-4">
          <ArrowLeft className="w-4 h-4" /> Voltar
        </Link>
        <PageHeader title="Detalhe da resposta" subtitle={`Entrevista completa em "${survey.name}"`} />

        <div className="rounded-xl border border-border bg-card p-6 shadow-soft mb-6">
          <div className="flex flex-wrap gap-6 text-sm">
            <Meta icon={User} label="Respondente" value={response.respondent} />
            <Meta icon={Calendar} label="Data" value={formatDate(response.date)} />
            <Meta icon={Clock} label="Duração" value={`${response.durationMin} min`} />
          </div>
        </div>

        <div className="space-y-5">
          {survey.questions.map((q) => {
            const a = response.answers.find((a) => a.questionId === q.id);
            return (
              <div key={q.id} className="space-y-2">
                <div className="flex gap-3">
                  <div className="w-8 h-8 shrink-0 rounded-lg bg-muted flex items-center justify-center text-xs font-medium text-muted-foreground">
                    IA
                  </div>
                  <div className="max-w-[80%] rounded-2xl rounded-tl-sm bg-muted px-4 py-2.5 text-sm">{q.text}</div>
                </div>
                {a && (
                  <div className="flex justify-end">
                    <div className="max-w-[80%] rounded-2xl rounded-tr-sm bg-primary px-4 py-2.5 text-sm text-primary-foreground">{a.text}</div>
                  </div>
                )}
              </div>
            );
          })}
        </div>
      </div>
    </AppShell>
  );
}

function Meta({ icon: Icon, label, value }: { icon: React.ComponentType<{ className?: string }>; label: string; value: string }) {
  return (
    <div className="flex items-center gap-2">
      <Icon className="w-4 h-4 text-muted-foreground" />
      <div>
        <div className="text-xs text-muted-foreground">{label}</div>
        <div className="font-medium">{value}</div>
      </div>
    </div>
  );
}
