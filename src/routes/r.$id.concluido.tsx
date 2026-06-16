import { createFileRoute } from "@tanstack/react-router";
import { CheckCircle2, Sparkles } from "lucide-react";
import { useState } from "react";

export const Route = createFileRoute("/r/$id/concluido")({
  head: () => ({ meta: [{ title: "Obrigado!" }] }),
  component: DonePage,
});

function DonePage() {
  const [feedback, setFeedback] = useState("");
  const [sent, setSent] = useState(false);

  return (
    <div className="min-h-screen bg-gradient-to-br from-primary-soft/40 via-background to-accent/40 flex items-center justify-center p-6">
      <div className="w-full max-w-lg rounded-2xl border border-border bg-card p-8 shadow-elevated text-center">
        <div className="w-16 h-16 mx-auto rounded-full bg-success/10 flex items-center justify-center">
          <CheckCircle2 className="w-8 h-8 text-success" />
        </div>
        <h1 className="text-2xl font-semibold tracking-tight mt-5">Pesquisa concluída!</h1>
        <p className="text-muted-foreground mt-2 leading-relaxed">
          Muito obrigada por compartilhar sua opinião. Suas respostas são essenciais para que possamos
          melhorar continuamente.
        </p>

        <div className="mt-8 text-left">
          <label className="text-sm font-medium">Quer deixar um feedback adicional?</label>
          {sent ? (
            <div className="mt-2 rounded-lg bg-success/10 text-success px-3 py-3 text-sm flex items-center gap-2">
              <Sparkles className="w-4 h-4" /> Feedback enviado, obrigada!
            </div>
          ) : (
            <>
              <textarea
                value={feedback}
                onChange={(e) => setFeedback(e.target.value)}
                rows={3}
                placeholder="Opcional"
                className="mt-1.5 w-full rounded-lg border border-input bg-card px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
              />
              <button
                onClick={() => setSent(true)}
                disabled={!feedback.trim()}
                className="mt-3 w-full rounded-lg bg-primary py-2.5 text-sm font-medium text-primary-foreground shadow-pop hover:opacity-90 transition disabled:opacity-40"
              >
                Enviar feedback
              </button>
            </>
          )}
        </div>

        <button
          onClick={() => window.close()}
          className="mt-4 w-full rounded-lg border border-input bg-card py-2.5 text-sm hover:bg-muted transition"
        >
          Encerrar
        </button>
      </div>
    </div>
  );
}
