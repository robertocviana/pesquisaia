import { createFileRoute, Link, useNavigate } from "@tanstack/react-router";
import { Sparkles } from "lucide-react";
import { useState, type FormEvent } from "react";

export const Route = createFileRoute("/cadastro")({
  head: () => ({
    meta: [
      { title: "Criar conta — PesquisaIA" },
      { name: "description", content: "Crie sua conta PesquisaIA e comece a fazer pesquisas inteligentes." },
    ],
  }),
  component: SignupPage,
});

function SignupPage() {
  const navigate = useNavigate();
  const [form, setForm] = useState({ name: "", email: "", pwd: "", pwd2: "" });

  function onSubmit(e: FormEvent) {
    e.preventDefault();
    navigate({ to: "/dashboard" });
  }

  function field<K extends keyof typeof form>(key: K, label: string, type = "text") {
    return (
      <div>
        <label className="text-sm font-medium">{label}</label>
        <input
          type={type}
          value={form[key]}
          onChange={(e) => setForm({ ...form, [key]: e.target.value })}
          className="mt-1.5 w-full rounded-lg border border-input bg-card px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
        />
      </div>
    );
  }

  return (
    <div className="min-h-screen flex items-center justify-center p-6 bg-gradient-to-br from-primary-soft/50 via-background to-accent/50">
      <form onSubmit={onSubmit} className="w-full max-w-md bg-card rounded-2xl border border-border p-8 shadow-elevated">
        <div className="flex items-center gap-2 mb-6">
          <div className="w-9 h-9 rounded-xl bg-primary flex items-center justify-center">
            <Sparkles className="w-5 h-5 text-primary-foreground" />
          </div>
          <span className="font-semibold text-lg">PesquisaIA</span>
        </div>
        <h1 className="text-2xl font-semibold tracking-tight">Criar conta</h1>
        <p className="text-sm text-muted-foreground mt-1">Leva menos de um minuto.</p>

        <div className="space-y-4 mt-6">
          {field("name", "Nome")}
          {field("email", "E-mail", "email")}
          {field("pwd", "Senha", "password")}
          {field("pwd2", "Confirmar senha", "password")}

          <button type="submit" className="w-full rounded-lg bg-primary py-2.5 text-sm font-medium text-primary-foreground shadow-pop hover:opacity-90 transition">
            Criar conta
          </button>
        </div>

        <p className="text-center text-sm text-muted-foreground mt-6">
          Já possuo conta. <Link to="/" className="text-primary font-medium hover:underline">Entrar</Link>
        </p>
      </form>
    </div>
  );
}
