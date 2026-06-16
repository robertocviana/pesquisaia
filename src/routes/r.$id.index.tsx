import { createFileRoute, Link } from "@tanstack/react-router";
import { useSurvey } from "@/hooks/use-surveys";
import { Sparkles, ArrowRight, Clock, ShieldCheck } from "lucide-react";

export const Route = createFileRoute("/r/$id/")({
  head: () => ({ meta: [{ title: "Participar da pesquisa" }] }),
  component: WelcomePage,
});

function WelcomePage() {
  const { id } = Route.useParams();
  const survey = useSurvey(id);

  return (
    <div className="min-h-screen bg-gradient-to-br from-primary-soft/40 via-background to-accent/40 flex items-center justify-center p-6">
      <div className="w-full max-w-lg rounded-2xl border border-border bg-card p-8 shadow-elevated">
        <div className="flex items-center gap-2 mb-8">
          <div className="w-9 h-9 rounded-xl bg-primary flex items-center justify-center">
            <Sparkles className="w-5 h-5 text-primary-foreground" />
          </div>
          <span className="font-semibold">PesquisaIA</span>
        </div>

        <h1 className="text-2xl font-semibold tracking-tight">{survey?.name ?? "Pesquisa"}</h1>
        <p className="text-muted-foreground mt-3 leading-relaxed">
          Olá! Estamos coletando opiniões para melhorar a nossa experiência. A conversa é informal e
          leva poucos minutos. Suas respostas são confidenciais.
        </p>

        <div className="mt-6 grid grid-cols-2 gap-3">
          <div className="rounded-lg border border-border p-3 flex items-center gap-2 text-sm">
            <Clock className="w-4 h-4 text-primary" /> ~3 min
          </div>
          <div className="rounded-lg border border-border p-3 flex items-center gap-2 text-sm">
            <ShieldCheck className="w-4 h-4 text-primary" /> Confidencial
          </div>
        </div>

        <Link
          to="/r/$id/chat"
          params={{ id }}
          className="mt-8 w-full inline-flex items-center justify-center gap-2 rounded-lg bg-primary py-3 text-sm font-medium text-primary-foreground shadow-pop hover:opacity-90 transition"
        >
          Iniciar pesquisa <ArrowRight className="w-4 h-4" />
        </Link>
      </div>
    </div>
  );
}
