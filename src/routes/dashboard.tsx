import { createFileRoute, Link } from "@tanstack/react-router";
import { AppShell, PageHeader, StatusBadge } from "@/components/app-shell";
import { useSurveys } from "@/hooks/use-surveys";
import { formatDate } from "@/lib/mock-data";
import { FileText, CheckCircle2, XCircle, MessagesSquare, Plus, ArrowRight } from "lucide-react";

export const Route = createFileRoute("/dashboard")({
  head: () => ({ meta: [{ title: "Dashboard — PesquisaIA" }] }),
  component: Dashboard,
});

function Dashboard() {
  const surveys = useSurveys();
  const total = surveys.length;
  const ativas = surveys.filter((s) => s.status === "ativa").length;
  const encerradas = surveys.filter((s) => s.status === "encerrada").length;
  const respostas = surveys.reduce((acc, s) => acc + s.responses.length, 0);

  const stats = [
    { label: "Total de pesquisas", value: total, icon: FileText },
    { label: "Pesquisas ativas", value: ativas, icon: CheckCircle2 },
    { label: "Encerradas", value: encerradas, icon: XCircle },
    { label: "Respostas recebidas", value: respostas, icon: MessagesSquare },
  ];

  return (
    <AppShell>
      <div className="max-w-6xl mx-auto p-6 sm:p-10">
        <PageHeader
          title="Olá, Ana 👋"
          subtitle="Aqui está um resumo das suas pesquisas."
          actions={
            <Link to="/pesquisas/nova" className="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground shadow-pop hover:opacity-90 transition">
              <Plus className="w-4 h-4" /> Nova pesquisa
            </Link>
          }
        />

        <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
          {stats.map(({ label, value, icon: Icon }) => (
            <div key={label} className="rounded-xl border border-border bg-card p-5 shadow-soft">
              <div className="flex items-center justify-between">
                <span className="text-xs uppercase tracking-wide text-muted-foreground">{label}</span>
                <Icon className="w-4 h-4 text-muted-foreground" />
              </div>
              <div className="mt-3 text-3xl font-semibold tracking-tight">{value}</div>
            </div>
          ))}
        </div>

        <div className="rounded-xl border border-border bg-card shadow-soft overflow-hidden">
          <div className="px-5 py-4 border-b border-border flex items-center justify-between">
            <h2 className="font-semibold">Pesquisas recentes</h2>
            <Link to="/pesquisas" className="text-sm text-primary hover:underline inline-flex items-center gap-1">
              Ver todas <ArrowRight className="w-3.5 h-3.5" />
            </Link>
          </div>
          <ul className="divide-y divide-border">
            {surveys.slice(0, 5).map((s) => (
              <li key={s.id} className="px-5 py-4 flex items-center gap-4 hover:bg-muted/40 transition">
                <div className="flex-1 min-w-0">
                  <div className="flex items-center gap-2.5">
                    <span className="font-medium truncate">{s.name}</span>
                    <StatusBadge status={s.status} />
                  </div>
                  <div className="text-xs text-muted-foreground mt-1">
                    {s.responses.length} respostas · criada em {formatDate(s.createdAt)}
                  </div>
                </div>
                <Link
                  to="/pesquisas/$id"
                  params={{ id: s.id }}
                  className="text-sm text-primary hover:underline"
                >
                  Visualizar
                </Link>
              </li>
            ))}
            {surveys.length === 0 && (
              <li className="px-5 py-10 text-center text-sm text-muted-foreground">
                Você ainda não criou nenhuma pesquisa.
              </li>
            )}
          </ul>
        </div>
      </div>
    </AppShell>
  );
}
