import { createFileRoute, Link } from "@tanstack/react-router";
import { AppShell, PageHeader, StatusBadge } from "@/components/app-shell";
import { useSurveys } from "@/hooks/use-surveys";
import { deleteSurvey, formatDate, upsertSurvey } from "@/lib/mock-data";
import { useState } from "react";
import { Plus, Copy, Archive, Trash2, ExternalLink } from "lucide-react";

export const Route = createFileRoute("/pesquisas/")({
  head: () => ({ meta: [{ title: "Minhas Pesquisas — PesquisaIA" }] }),
  component: MySurveys,
});

const filters = [
  { key: "todas", label: "Todas" },
  { key: "ativa", label: "Ativas" },
  { key: "encerrada", label: "Encerradas" },
  { key: "rascunho", label: "Rascunhos" },
] as const;

function MySurveys() {
  const surveys = useSurveys();
  const [filter, setFilter] = useState<(typeof filters)[number]["key"]>("todas");
  const filtered = filter === "todas" ? surveys : surveys.filter((s) => s.status === filter);

  return (
    <AppShell>
      <div className="max-w-6xl mx-auto p-6 sm:p-10">
        <PageHeader
          title="Minhas pesquisas"
          subtitle="Gerencie todas as suas pesquisas em um só lugar."
          actions={
            <Link to="/pesquisas/nova" className="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground shadow-pop hover:opacity-90 transition">
              <Plus className="w-4 h-4" /> Nova pesquisa
            </Link>
          }
        />

        <div className="flex flex-wrap gap-2 mb-6">
          {filters.map((f) => (
            <button
              key={f.key}
              onClick={() => setFilter(f.key)}
              className={`rounded-full px-4 py-1.5 text-sm transition ${
                filter === f.key
                  ? "bg-primary text-primary-foreground"
                  : "bg-card border border-border text-muted-foreground hover:text-foreground"
              }`}
            >
              {f.label}
            </button>
          ))}
        </div>

        <div className="grid gap-3">
          {filtered.map((s) => (
            <div key={s.id} className="rounded-xl border border-border bg-card p-5 shadow-soft hover:shadow-elevated transition">
              <div className="flex items-start justify-between gap-4">
                <div className="flex-1 min-w-0">
                  <div className="flex items-center gap-2.5 flex-wrap">
                    <h3 className="font-semibold truncate">{s.name}</h3>
                    <StatusBadge status={s.status} />
                  </div>
                  <p className="text-sm text-muted-foreground mt-1">
                    {s.responses.length} respostas · criada em {formatDate(s.createdAt)}
                  </p>
                </div>
                <div className="flex items-center gap-1.5">
                  <Link
                    to="/pesquisas/$id"
                    params={{ id: s.id }}
                    className="inline-flex items-center gap-1.5 rounded-lg border border-input bg-card px-3 py-1.5 text-sm hover:bg-muted transition"
                  >
                    <ExternalLink className="w-3.5 h-3.5" /> Abrir
                  </Link>
                  <IconButton
                    title="Duplicar"
                    onClick={() => {
                      upsertSurvey({ ...s, id: "s-" + Math.random().toString(36).slice(2, 8), name: s.name + " (cópia)", createdAt: new Date().toISOString() });
                    }}
                  >
                    <Copy className="w-4 h-4" />
                  </IconButton>
                  <IconButton title="Arquivar" onClick={() => upsertSurvey({ ...s, status: "encerrada" })}>
                    <Archive className="w-4 h-4" />
                  </IconButton>
                  <IconButton title="Excluir" onClick={() => { if (confirm("Excluir esta pesquisa?")) deleteSurvey(s.id); }}>
                    <Trash2 className="w-4 h-4 text-destructive" />
                  </IconButton>
                </div>
              </div>
            </div>
          ))}
          {filtered.length === 0 && (
            <div className="rounded-xl border border-dashed border-border p-12 text-center text-sm text-muted-foreground">
              Nenhuma pesquisa nesta categoria.
            </div>
          )}
        </div>
      </div>
    </AppShell>
  );
}

function IconButton({ children, onClick, title }: { children: React.ReactNode; onClick: () => void; title: string }) {
  return (
    <button title={title} onClick={onClick} className="p-2 rounded-lg border border-input bg-card hover:bg-muted transition">
      {children}
    </button>
  );
}
