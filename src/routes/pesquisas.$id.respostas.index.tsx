import { createFileRoute, Link } from "@tanstack/react-router";
import { AppShell, PageHeader } from "@/components/app-shell";
import { useSurvey } from "@/hooks/use-surveys";
import { formatDate } from "@/lib/mock-data";
import { ArrowLeft, Eye, Filter } from "lucide-react";
import { useState } from "react";

export const Route = createFileRoute("/pesquisas/$id/respostas/")({
  head: () => ({ meta: [{ title: "Respostas — PesquisaIA" }] }),
  component: ResponsesList,
});

function ResponsesList() {
  const { id } = Route.useParams();
  const survey = useSurvey(id);
  const [query, setQuery] = useState("");

  if (!survey) return <AppShell><div className="p-10">Pesquisa não encontrada.</div></AppShell>;

  const filtered = survey.responses.filter((r) => r.respondent.toLowerCase().includes(query.toLowerCase()));

  return (
    <AppShell>
      <div className="max-w-5xl mx-auto p-6 sm:p-10">
        <Link to="/pesquisas/$id" params={{ id }} className="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground mb-4">
          <ArrowLeft className="w-4 h-4" /> Voltar à pesquisa
        </Link>
        <PageHeader title="Respostas" subtitle={`${survey.responses.length} respostas em "${survey.name}"`} />

        <div className="rounded-xl border border-border bg-card shadow-soft overflow-hidden">
          <div className="px-4 py-3 border-b border-border flex flex-wrap items-center gap-3">
            <div className="relative flex-1 min-w-[200px]">
              <input
                placeholder="Buscar respondente..."
                value={query}
                onChange={(e) => setQuery(e.target.value)}
                className="w-full rounded-lg border border-input bg-card px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
              />
            </div>
            <button className="inline-flex items-center gap-1.5 rounded-lg border border-input bg-card px-3 py-2 text-sm hover:bg-muted transition">
              <Filter className="w-3.5 h-3.5" /> Data
            </button>
            <button className="inline-flex items-center gap-1.5 rounded-lg border border-input bg-card px-3 py-2 text-sm hover:bg-muted transition">
              <Filter className="w-3.5 h-3.5" /> Status
            </button>
          </div>

          <table className="w-full text-sm">
            <thead className="bg-muted/40 text-xs uppercase text-muted-foreground">
              <tr>
                <th className="text-left px-4 py-2.5 font-medium">Respondente</th>
                <th className="text-left px-4 py-2.5 font-medium">Data</th>
                <th className="text-left px-4 py-2.5 font-medium">Tempo</th>
                <th className="text-left px-4 py-2.5 font-medium">Status</th>
                <th className="px-4 py-2.5"></th>
              </tr>
            </thead>
            <tbody className="divide-y divide-border">
              {filtered.map((r) => (
                <tr key={r.id} className="hover:bg-muted/30 transition">
                  <td className="px-4 py-3 font-medium">{r.respondent}</td>
                  <td className="px-4 py-3 text-muted-foreground">{formatDate(r.date)}</td>
                  <td className="px-4 py-3 text-muted-foreground">{r.durationMin} min</td>
                  <td className="px-4 py-3">
                    <span className="inline-flex items-center rounded-full bg-success/10 text-success px-2 py-0.5 text-xs font-medium capitalize">
                      {r.status}
                    </span>
                  </td>
                  <td className="px-4 py-3 text-right">
                    <Link
                      to="/pesquisas/$id/respostas/$rid"
                      params={{ id, rid: r.id }}
                      className="inline-flex items-center gap-1.5 text-primary text-sm hover:underline"
                    >
                      <Eye className="w-3.5 h-3.5" /> Visualizar
                    </Link>
                  </td>
                </tr>
              ))}
              {filtered.length === 0 && (
                <tr>
                  <td colSpan={5} className="px-4 py-12 text-center text-muted-foreground">
                    Nenhuma resposta encontrada ainda.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </AppShell>
  );
}
