import { createFileRoute, Link, useNavigate } from "@tanstack/react-router";
import { Sparkles } from "lucide-react";
import { useState, type FormEvent } from "react";

export const Route = createFileRoute("/")({
  head: () => ({
    meta: [
      { title: "Entrar — PesquisaIA" },
      { name: "description", content: "Acesse sua conta PesquisaIA para criar pesquisas inteligentes em minutos." },
    ],
  }),
  component: LoginPage,
});

function LoginPage() {
  const navigate = useNavigate();
  const [email, setEmail] = useState("ana@empresa.com");
  const [pwd, setPwd] = useState("••••••••");

  function onSubmit(e: FormEvent) {
    e.preventDefault();
    navigate({ to: "/dashboard" });
  }

  return (
    <div className="min-h-screen grid lg:grid-cols-2">
      <div className="hidden lg:flex flex-col justify-between p-12 bg-gradient-to-br from-primary-soft via-background to-accent">
        <Link to="/" className="flex items-center gap-2">
          <div className="w-9 h-9 rounded-xl bg-primary flex items-center justify-center">
            <Sparkles className="w-5 h-5 text-primary-foreground" />
          </div>
          <span className="font-semibold text-lg">PesquisaIA</span>
        </Link>
        <div className="max-w-md">
          <h2 className="text-3xl font-semibold tracking-tight">Pesquisas inteligentes em minutos.</h2>
          <p className="mt-3 text-muted-foreground">Crie pesquisas conversando com a IA, colete respostas e veja relatórios automáticos com insights.</p>
        </div>
        <p className="text-xs text-muted-foreground">© 2026 PesquisaIA</p>
      </div>

      <div className="flex items-center justify-center p-6 sm:p-12">
        <form onSubmit={onSubmit} className="w-full max-w-sm">
          <div className="lg:hidden flex items-center gap-2 mb-8">
            <div className="w-9 h-9 rounded-xl bg-primary flex items-center justify-center">
              <Sparkles className="w-5 h-5 text-primary-foreground" />
            </div>
            <span className="font-semibold text-lg">PesquisaIA</span>
          </div>
          <h1 className="text-2xl font-semibold tracking-tight">Entrar</h1>
          <p className="text-sm text-muted-foreground mt-1">Bem-vindo de volta.</p>

          <div className="space-y-4 mt-8">
            <div>
              <label className="text-sm font-medium">E-mail</label>
              <input
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                className="mt-1.5 w-full rounded-lg border border-input bg-card px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
              />
            </div>
            <div>
              <div className="flex items-center justify-between">
                <label className="text-sm font-medium">Senha</label>
                <a href="#" className="text-xs text-primary hover:underline">Esqueci minha senha</a>
              </div>
              <input
                type="password"
                value={pwd}
                onChange={(e) => setPwd(e.target.value)}
                className="mt-1.5 w-full rounded-lg border border-input bg-card px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
              />
            </div>

            <button type="submit" className="w-full rounded-lg bg-primary py-2.5 text-sm font-medium text-primary-foreground shadow-pop hover:opacity-90 transition">
              Entrar
            </button>

            <div className="flex items-center gap-3 text-xs text-muted-foreground">
              <div className="h-px flex-1 bg-border" />
              <span>ou</span>
              <div className="h-px flex-1 bg-border" />
            </div>

            <button type="button" onClick={() => navigate({ to: "/dashboard" })} className="w-full rounded-lg border border-input bg-card py-2.5 text-sm font-medium hover:bg-muted transition flex items-center justify-center gap-2">
              <GoogleIcon /> Continuar com Google
            </button>
          </div>

          <p className="text-center text-sm text-muted-foreground mt-8">
            Não tem conta? <Link to="/cadastro" className="text-primary font-medium hover:underline">Criar conta</Link>
          </p>
        </form>
      </div>
    </div>
  );
}

function GoogleIcon() {
  return (
    <svg width="16" height="16" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.99.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.1c-.22-.66-.35-1.36-.35-2.1s.13-1.44.35-2.1V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.83z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84C6.71 7.31 9.14 5.38 12 5.38z"/></svg>
  );
}
