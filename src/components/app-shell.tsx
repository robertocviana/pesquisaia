import { Link, useRouterState } from "@tanstack/react-router";
import { LayoutDashboard, FileText, Settings, Plus, Sparkles } from "lucide-react";
import type { ReactNode } from "react";

const nav = [
  { to: "/dashboard", label: "Dashboard", icon: LayoutDashboard },
  { to: "/pesquisas", label: "Minhas Pesquisas", icon: FileText },
  { to: "/configuracoes", label: "Configurações", icon: Settings },
] as const;

export function AppShell({ children }: { children: ReactNode }) {
  const pathname = useRouterState({ select: (s) => s.location.pathname });

  return (
    <div className="min-h-screen flex bg-background">
      <aside className="hidden md:flex w-64 flex-col border-r border-sidebar-border bg-sidebar p-4 gap-1">
        <Link to="/dashboard" className="flex items-center gap-2 px-2 py-3 mb-4">
          <div className="w-8 h-8 rounded-lg bg-primary flex items-center justify-center">
            <Sparkles className="w-4 h-4 text-primary-foreground" />
          </div>
          <span className="font-semibold text-foreground">PesquisaIA</span>
        </Link>

        <Link
          to="/pesquisas/nova"
          className="mb-4 inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-3 py-2.5 text-sm font-medium text-primary-foreground shadow-pop hover:opacity-90 transition"
        >
          <Plus className="w-4 h-4" />
          Nova pesquisa
        </Link>

        {nav.map(({ to, label, icon: Icon }) => {
          const active = pathname.startsWith(to);
          return (
            <Link
              key={to}
              to={to}
              className={`flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm transition ${
                active
                  ? "bg-accent text-accent-foreground font-medium"
                  : "text-muted-foreground hover:bg-muted hover:text-foreground"
              }`}
            >
              <Icon className="w-4 h-4" />
              {label}
            </Link>
          );
        })}

        <div className="mt-auto border-t border-sidebar-border pt-4">
          <div className="flex items-center gap-3 px-2">
            <div className="w-9 h-9 rounded-full bg-primary-soft flex items-center justify-center text-sm font-medium text-primary">
              AC
            </div>
            <div className="flex-1 min-w-0">
              <div className="text-sm font-medium truncate">Ana Costa</div>
              <div className="text-xs text-muted-foreground truncate">ana@empresa.com</div>
            </div>
          </div>
        </div>
      </aside>

      <main className="flex-1 min-w-0">{children}</main>
    </div>
  );
}

export function PageHeader({
  title,
  subtitle,
  actions,
}: {
  title: string;
  subtitle?: string;
  actions?: ReactNode;
}) {
  return (
    <div className="flex flex-wrap items-end justify-between gap-4 mb-8">
      <div>
        <h1 className="text-2xl font-semibold tracking-tight text-foreground">{title}</h1>
        {subtitle && <p className="text-sm text-muted-foreground mt-1">{subtitle}</p>}
      </div>
      {actions && <div className="flex items-center gap-2">{actions}</div>}
    </div>
  );
}

export function StatusBadge({ status }: { status: "ativa" | "encerrada" | "rascunho" }) {
  const styles = {
    ativa: "bg-success/10 text-success border-success/20",
    encerrada: "bg-muted text-muted-foreground border-border",
    rascunho: "bg-warning/10 text-warning-foreground border-warning/20",
  } as const;
  return (
    <span className={`inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium capitalize ${styles[status]}`}>
      <span className={`w-1.5 h-1.5 rounded-full ${
        status === "ativa" ? "bg-success" : status === "encerrada" ? "bg-muted-foreground" : "bg-warning"
      }`} />
      {status}
    </span>
  );
}
