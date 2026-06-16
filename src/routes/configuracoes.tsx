import { createFileRoute } from "@tanstack/react-router";
import { AppShell, PageHeader } from "@/components/app-shell";
import { useState } from "react";
import { User, Lock, CreditCard, Settings as SettingsIcon, Sun, Moon } from "lucide-react";

export const Route = createFileRoute("/configuracoes")({
  head: () => ({ meta: [{ title: "Configurações — PesquisaIA" }] }),
  component: SettingsPage,
});

const tabs = [
  { key: "perfil", label: "Perfil", icon: User },
  { key: "seguranca", label: "Segurança", icon: Lock },
  { key: "assinatura", label: "Assinatura", icon: CreditCard },
  { key: "preferencias", label: "Preferências", icon: SettingsIcon },
] as const;

function SettingsPage() {
  const [tab, setTab] = useState<(typeof tabs)[number]["key"]>("perfil");
  const [theme, setTheme] = useState<"claro" | "escuro">("claro");

  return (
    <AppShell>
      <div className="max-w-4xl mx-auto p-6 sm:p-10">
        <PageHeader title="Configurações" subtitle="Gerencie sua conta e preferências." />

        <div className="grid md:grid-cols-[200px_1fr] gap-6">
          <nav className="space-y-1">
            {tabs.map((t) => (
              <button
                key={t.key}
                onClick={() => setTab(t.key)}
                className={`w-full flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm transition ${
                  tab === t.key ? "bg-accent text-accent-foreground font-medium" : "text-muted-foreground hover:bg-muted hover:text-foreground"
                }`}
              >
                <t.icon className="w-4 h-4" /> {t.label}
              </button>
            ))}
          </nav>

          <div className="rounded-xl border border-border bg-card p-6 shadow-soft">
            {tab === "perfil" && (
              <div className="space-y-5">
                <h3 className="font-semibold">Perfil</h3>
                <div className="flex items-center gap-4">
                  <div className="w-16 h-16 rounded-full bg-primary-soft flex items-center justify-center text-xl font-semibold text-primary">AC</div>
                  <button className="rounded-lg border border-input bg-card px-3 py-1.5 text-sm hover:bg-muted transition">Alterar foto</button>
                </div>
                <SField label="Nome" value="Ana Costa" />
                <SField label="E-mail" value="ana@empresa.com" />
                <button className="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-pop hover:opacity-90 transition">Salvar alterações</button>
              </div>
            )}
            {tab === "seguranca" && (
              <div className="space-y-5">
                <h3 className="font-semibold">Segurança</h3>
                <SField label="Senha atual" value="" type="password" />
                <SField label="Nova senha" value="" type="password" />
                <SField label="Confirmar nova senha" value="" type="password" />
                <button className="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-pop hover:opacity-90 transition">Alterar senha</button>
              </div>
            )}
            {tab === "assinatura" && (
              <div className="space-y-5">
                <h3 className="font-semibold">Assinatura</h3>
                <div className="rounded-lg border border-border bg-gradient-to-br from-primary-soft to-card p-5">
                  <div className="text-xs uppercase tracking-wide text-muted-foreground">Plano atual</div>
                  <div className="mt-1 text-2xl font-semibold">Pro</div>
                  <p className="text-sm text-muted-foreground mt-1">Pesquisas ilimitadas · Relatórios com IA · R$ 89/mês</p>
                  <button className="mt-4 rounded-lg border border-input bg-card px-3 py-1.5 text-sm hover:bg-muted transition">Gerenciar plano</button>
                </div>
                <div>
                  <h4 className="text-sm font-medium mb-2">Histórico</h4>
                  <ul className="divide-y divide-border rounded-lg border border-border">
                    {["Nov 2026", "Out 2026", "Set 2026"].map((m) => (
                      <li key={m} className="flex items-center justify-between px-4 py-2.5 text-sm">
                        <span>{m}</span>
                        <span className="text-muted-foreground">R$ 89,00</span>
                      </li>
                    ))}
                  </ul>
                </div>
              </div>
            )}
            {tab === "preferencias" && (
              <div className="space-y-5">
                <h3 className="font-semibold">Preferências</h3>
                <div>
                  <label className="text-sm font-medium">Idioma</label>
                  <select className="mt-1.5 w-full rounded-lg border border-input bg-card px-3 py-2 text-sm">
                    <option>Português (BR)</option>
                    <option>English</option>
                    <option>Español</option>
                  </select>
                </div>
                <div>
                  <label className="text-sm font-medium block mb-2">Tema</label>
                  <div className="grid grid-cols-2 gap-2">
                    {(["claro", "escuro"] as const).map((t) => (
                      <button
                        key={t}
                        onClick={() => { setTheme(t); document.documentElement.classList.toggle("dark", t === "escuro"); }}
                        className={`flex items-center gap-2 rounded-lg border px-3 py-2.5 text-sm transition capitalize ${
                          theme === t ? "border-primary bg-primary-soft" : "border-input bg-card hover:bg-muted"
                        }`}
                      >
                        {t === "claro" ? <Sun className="w-4 h-4" /> : <Moon className="w-4 h-4" />} {t}
                      </button>
                    ))}
                  </div>
                </div>
              </div>
            )}
          </div>
        </div>
      </div>
    </AppShell>
  );
}

function SField({ label, value, type = "text" }: { label: string; value: string; type?: string }) {
  return (
    <div>
      <label className="text-sm font-medium">{label}</label>
      <input defaultValue={value} type={type} className="mt-1.5 w-full rounded-lg border border-input bg-card px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring" />
    </div>
  );
}
