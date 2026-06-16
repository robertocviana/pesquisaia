import { createFileRoute, Link } from "@tanstack/react-router";
import { AppShell, PageHeader, StatusBadge } from "@/components/app-shell";
import { useSurvey } from "@/hooks/use-surveys";
import { formatDate, upsertSurvey } from "@/lib/mock-data";
import { Copy, QrCode, BarChart3, MessagesSquare, XCircle, Check } from "lucide-react";
import { useState } from "react";

export const Route = createFileRoute("/pesquisas/$id/")({
  head: () => ({ meta: [{ title: "Pesquisa publicada — PesquisaIA" }] }),
  component: PublishedPage,
});

function PublishedPage() {
  const { id } = Route.useParams();
  const survey = useSurvey(id);
  const [copied, setCopied] = useState(false);

  if (!survey) return <AppShell><div className="p-10">Pesquisa não encontrada.</div></AppShell>;

  const link = typeof window !== "undefined" ? `${window.location.origin}/r/${survey.id}` : `/r/${survey.id}`;
  const progress = Math.min(100, Math.round((survey.responses.length / Math.max(1, survey.goal)) * 100));

  return (
    <AppShell>
      <div className="max-w-5xl mx-auto p-6 sm:p-10">
        <PageHeader
          title={survey.name}
          subtitle={`Criada em ${formatDate(survey.createdAt)}`}
          actions={<StatusBadge status={survey.status} />}
        />

        <div className="grid lg:grid-cols-3 gap-5 mb-6">
          <div className="lg:col-span-2 rounded-xl border border-border bg-card p-6 shadow-soft">
            <h3 className="font-semibold mb-1">Compartilhe sua pesquisa</h3>
            <p className="text-sm text-muted-foreground mb-4">Envie o link ou QR code para seus respondentes.</p>

            <div className="flex items-center gap-2 rounded-lg border border-border bg-muted/40 p-2">
              <input readOnly value={link} className="flex-1 bg-transparent text-sm px-2 focus:outline-none" />
              <button
                onClick={() => { navigator.clipboard.writeText(link); setCopied(true); setTimeout(() => setCopied(false), 1500); }}
                className="inline-flex items-center gap-1.5 rounded-md bg-primary px-3 py-1.5 text-xs font-medium text-primary-foreground hover:opacity-90 transition"
              >
                {copied ? <><Check className="w-3.5 h-3.5" /> Copiado</> : <><Copy className="w-3.5 h-3.5" /> Copiar</>}
              </button>
            </div>

            <div className="mt-6 flex items-center gap-4">
              <div className="w-32 h-32 rounded-lg border border-border bg-background flex items-center justify-center">
                <QrCode className="w-20 h-20 text-foreground" strokeWidth={1.2} />
              </div>
              <div className="text-sm text-muted-foreground">
                <div className="font-medium text-foreground mb-1">QR Code</div>
                Imprima ou exiba o código em telas para captar respostas presenciais.
              </div>
            </div>
          </div>

          <div className="rounded-xl border border-border bg-card p-6 shadow-soft">
            <h3 className="font-semibold mb-4">Estatísticas rápidas</h3>
            <div className="space-y-4">
              <Stat label="Respostas recebidas" value={survey.responses.length} />
              <Stat label="Meta definida" value={survey.goal} />
              <div>
                <div className="flex items-center justify-between text-sm mb-2">
                  <span className="text-muted-foreground">Progresso</span>
                  <span className="font-medium">{progress}%</span>
                </div>
                <div className="h-2 rounded-full bg-muted overflow-hidden">
                  <div className="h-full bg-primary transition-all" style={{ width: `${progress}%` }} />
                </div>
              </div>
            </div>
          </div>
        </div>

        <div className="flex flex-wrap gap-2">
          <Link to="/pesquisas/$id/respostas" params={{ id: survey.id }} className="inline-flex items-center gap-2 rounded-lg border border-input bg-card px-4 py-2.5 text-sm hover:bg-muted transition">
            <MessagesSquare className="w-4 h-4" /> Ver respostas
          </Link>
          <Link to="/pesquisas/$id/relatorio" params={{ id: survey.id }} className="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground shadow-pop hover:opacity-90 transition">
            <BarChart3 className="w-4 h-4" /> Ver relatório
          </Link>
          {survey.status === "ativa" && (
            <button
              onClick={() => { if (confirm("Encerrar esta pesquisa?")) upsertSurvey({ ...survey, status: "encerrada" }); }}
              className="ml-auto inline-flex items-center gap-2 rounded-lg border border-destructive/30 text-destructive bg-card px-4 py-2.5 text-sm hover:bg-destructive/5 transition"
            >
              <XCircle className="w-4 h-4" /> Encerrar pesquisa
            </button>
          )}
        </div>
      </div>
    </AppShell>
  );
}

function Stat({ label, value }: { label: string; value: number }) {
  return (
    <div className="flex items-center justify-between">
      <span className="text-sm text-muted-foreground">{label}</span>
      <span className="text-lg font-semibold">{value}</span>
    </div>
  );
}
