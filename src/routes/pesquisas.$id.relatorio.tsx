import { createFileRoute, Link } from "@tanstack/react-router";
import { AppShell, PageHeader } from "@/components/app-shell";
import { useSurvey } from "@/hooks/use-surveys";
import { ArrowLeft, Download, Share2, TrendingUp, Sparkles, Quote } from "lucide-react";

export const Route = createFileRoute("/pesquisas/$id/relatorio")({
  head: () => ({ meta: [{ title: "Relatório — PesquisaIA" }] }),
  component: ReportPage,
});

const insights = [
  { title: "Atendimento é o ponto forte mais citado", count: 14 },
  { title: "Tempo de entrega aparece como principal fricção", count: 9 },
  { title: "Recomendação espontânea é alta entre usuários >30 dias", count: 7 },
];

const highlights = [
  { who: "Maria Silva", quote: "Suporte resolveu na primeira tentativa, foi rápido e simpático." },
  { who: "João Pereira", quote: "Produto bom, mas a comunicação sobre o status do pedido precisa melhorar." },
  { who: "Ana Costa", quote: "Tudo funcionou como esperado, recomendo de olhos fechados." },
];

const distribution = [
  { label: "Muito satisfeito", value: 52 },
  { label: "Satisfeito", value: 28 },
  { label: "Neutro", value: 12 },
  { label: "Insatisfeito", value: 8 },
];

const evolution = [3, 5, 8, 7, 12, 14, 10, 16, 19, 22, 18, 25];

function ReportPage() {
  const { id } = Route.useParams();
  const survey = useSurvey(id);
  if (!survey) return <AppShell><div className="p-10">Pesquisa não encontrada.</div></AppShell>;

  return (
    <AppShell>
      <div className="max-w-5xl mx-auto p-6 sm:p-10">
        <Link to="/pesquisas/$id" params={{ id }} className="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground mb-4">
          <ArrowLeft className="w-4 h-4" /> Voltar à pesquisa
        </Link>
        <PageHeader
          title="Relatório"
          subtitle={`Análise consolidada — ${survey.name}`}
          actions={
            <>
              <button className="inline-flex items-center gap-1.5 rounded-lg border border-input bg-card px-3 py-2 text-sm hover:bg-muted transition">
                <Share2 className="w-4 h-4" /> Compartilhar
              </button>
              <button className="inline-flex items-center gap-1.5 rounded-lg bg-primary px-3 py-2 text-sm font-medium text-primary-foreground shadow-pop hover:opacity-90 transition">
                <Download className="w-4 h-4" /> Exportar PDF
              </button>
            </>
          }
        />

        {/* Resumo executivo */}
        <section className="rounded-xl border border-border bg-gradient-to-br from-primary-soft/60 to-card p-6 shadow-soft mb-6">
          <div className="flex items-center gap-2 mb-3">
            <Sparkles className="w-4 h-4 text-primary" />
            <h2 className="font-semibold">Resumo executivo</h2>
          </div>
          <div className="grid sm:grid-cols-3 gap-4 mb-4">
            <KPI label="Total de respostas" value={survey.responses.length || 47} />
            <KPI label="Taxa de conclusão" value="87%" />
            <KPI label="Satisfação média" value="8.4 / 10" />
          </div>
          <p className="text-sm text-muted-foreground leading-relaxed">
            A maioria dos respondentes está satisfeita com o atendimento, destacando a velocidade do suporte.
            A principal oportunidade está em melhorar a comunicação sobre status de entregas, mencionada em
            cerca de 19% das respostas.
          </p>
        </section>

        {/* Temas identificados */}
        <section className="rounded-xl border border-border bg-card p-6 shadow-soft mb-6">
          <h2 className="font-semibold mb-4">Temas identificados</h2>
          <ul className="space-y-2">
            {insights.map((i) => (
              <li key={i.title} className="flex items-center gap-3 p-3 rounded-lg border border-border">
                <TrendingUp className="w-4 h-4 text-primary" />
                <span className="flex-1 text-sm">{i.title}</span>
                <span className="text-xs text-muted-foreground">{i.count} menções</span>
              </li>
            ))}
          </ul>
        </section>

        {/* Gráficos */}
        <div className="grid lg:grid-cols-2 gap-5 mb-6">
          <div className="rounded-xl border border-border bg-card p-6 shadow-soft">
            <h3 className="font-semibold mb-4">Distribuição das respostas</h3>
            <div className="space-y-3">
              {distribution.map((d) => (
                <div key={d.label}>
                  <div className="flex justify-between text-sm mb-1">
                    <span>{d.label}</span>
                    <span className="text-muted-foreground">{d.value}%</span>
                  </div>
                  <div className="h-2 rounded-full bg-muted overflow-hidden">
                    <div className="h-full bg-primary" style={{ width: `${d.value}%` }} />
                  </div>
                </div>
              ))}
            </div>
          </div>

          <div className="rounded-xl border border-border bg-card p-6 shadow-soft">
            <h3 className="font-semibold mb-4">Evolução das respostas</h3>
            <div className="h-40 flex items-end gap-1.5">
              {evolution.map((v, i) => (
                <div
                  key={i}
                  className="flex-1 rounded-t bg-primary/80 hover:bg-primary transition"
                  style={{ height: `${(v / Math.max(...evolution)) * 100}%` }}
                  title={`${v} respostas`}
                />
              ))}
            </div>
            <div className="mt-3 text-xs text-muted-foreground">Últimos 12 dias</div>
          </div>
        </div>

        {/* Comentários relevantes */}
        <section className="rounded-xl border border-border bg-card p-6 shadow-soft">
          <h2 className="font-semibold mb-4">Comentários relevantes</h2>
          <div className="space-y-3">
            {highlights.map((h) => (
              <div key={h.who} className="flex gap-3 p-4 rounded-lg bg-muted/40">
                <Quote className="w-4 h-4 text-primary shrink-0 mt-1" />
                <div>
                  <p className="text-sm leading-relaxed">"{h.quote}"</p>
                  <p className="text-xs text-muted-foreground mt-2">— {h.who}</p>
                </div>
              </div>
            ))}
          </div>
        </section>
      </div>
    </AppShell>
  );
}

function KPI({ label, value }: { label: string; value: string | number }) {
  return (
    <div className="rounded-lg bg-card border border-border p-4">
      <div className="text-xs uppercase tracking-wide text-muted-foreground">{label}</div>
      <div className="mt-1.5 text-2xl font-semibold tracking-tight">{value}</div>
    </div>
  );
}
