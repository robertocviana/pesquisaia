// Mock in-memory data for the prototype. No backend.
export type SurveyStatus = "ativa" | "encerrada" | "rascunho";

export type Question = {
  id: string;
  text: string;
};

export type Answer = {
  questionId: string;
  text: string;
};

export type Response = {
  id: string;
  respondent: string;
  date: string; // ISO
  durationMin: number;
  status: "completa" | "parcial";
  answers: Answer[];
};

export type Survey = {
  id: string;
  name: string;
  objective: string;
  audience: string;
  status: SurveyStatus;
  createdAt: string; // ISO
  goal: number;
  questions: Question[];
  responses: Response[];
  // Per-survey AI chat history (creation conversation)
  chat: { role: "assistant" | "user"; text: string }[];
};

const today = new Date();
const daysAgo = (n: number) => new Date(today.getTime() - n * 86400000).toISOString();

export const seedSurveys: Survey[] = [
  {
    id: "s-001",
    name: "Satisfação de clientes Q4",
    objective: "Medir NPS e identificar pontos de fricção no atendimento.",
    audience: "Clientes ativos nos últimos 90 dias",
    status: "ativa",
    createdAt: daysAgo(7),
    goal: 200,
    questions: [
      { id: "q1", text: "Em uma escala de 0 a 10, o quanto recomendaria nosso produto?" },
      { id: "q2", text: "Qual foi o principal motivo da sua nota?" },
      { id: "q3", text: "O que poderíamos melhorar?" },
      { id: "q4", text: "Como você descreveria nosso atendimento em uma palavra?" },
    ],
    responses: [
      {
        id: "r1",
        respondent: "Maria Silva",
        date: daysAgo(1),
        durationMin: 4,
        status: "completa",
        answers: [
          { questionId: "q1", text: "9" },
          { questionId: "q2", text: "Atendimento rápido e produto confiável." },
          { questionId: "q3", text: "Mais opções de pagamento." },
          { questionId: "q4", text: "Atencioso" },
        ],
      },
      {
        id: "r2",
        respondent: "João Pereira",
        date: daysAgo(2),
        durationMin: 3,
        status: "completa",
        answers: [
          { questionId: "q1", text: "7" },
          { questionId: "q2", text: "Bom no geral, mas a entrega atrasou." },
          { questionId: "q3", text: "Comunicação sobre status do pedido." },
          { questionId: "q4", text: "Inconsistente" },
        ],
      },
      {
        id: "r3",
        respondent: "Ana Costa",
        date: daysAgo(3),
        durationMin: 5,
        status: "completa",
        answers: [
          { questionId: "q1", text: "10" },
          { questionId: "q2", text: "Suporte resolveu na primeira tentativa." },
          { questionId: "q3", text: "Nada de imediato." },
          { questionId: "q4", text: "Excelente" },
        ],
      },
    ],
    chat: [
      { role: "assistant", text: "Olá! Vamos criar sua pesquisa. Qual o objetivo principal?" },
      { role: "user", text: "Quero medir satisfação dos clientes." },
      { role: "assistant", text: "Ótimo. Qual público vai responder?" },
      { role: "user", text: "Clientes ativos dos últimos 90 dias." },
    ],
  },
  {
    id: "s-002",
    name: "Pesquisa de produto — onboarding",
    objective: "Entender pontos de atrito no onboarding de novos usuários.",
    audience: "Usuários que se cadastraram nas últimas 2 semanas",
    status: "ativa",
    createdAt: daysAgo(14),
    goal: 100,
    questions: [
      { id: "q1", text: "O que te motivou a se cadastrar?" },
      { id: "q2", text: "Qual etapa do onboarding foi mais confusa?" },
    ],
    responses: [
      {
        id: "r1",
        respondent: "Carlos Mendes",
        date: daysAgo(5),
        durationMin: 2,
        status: "completa",
        answers: [
          { questionId: "q1", text: "Indicação de um amigo." },
          { questionId: "q2", text: "A conexão com o calendário." },
        ],
      },
    ],
    chat: [],
  },
  {
    id: "s-003",
    name: "Feedback evento anual",
    objective: "Coletar feedback dos participantes do evento.",
    audience: "Participantes do evento de 2025",
    status: "encerrada",
    createdAt: daysAgo(60),
    goal: 300,
    questions: [
      { id: "q1", text: "Como avalia a organização?" },
    ],
    responses: [],
    chat: [],
  },
  {
    id: "s-004",
    name: "Rascunho — Pesquisa de marca",
    objective: "",
    audience: "",
    status: "rascunho",
    createdAt: daysAgo(2),
    goal: 50,
    questions: [],
    responses: [],
    chat: [],
  },
];

const STORAGE_KEY = "pesquisas-mock-v1";

function load(): Survey[] {
  if (typeof window === "undefined") return seedSurveys;
  try {
    const raw = window.localStorage.getItem(STORAGE_KEY);
    if (!raw) return seedSurveys;
    return JSON.parse(raw) as Survey[];
  } catch {
    return seedSurveys;
  }
}

function save(list: Survey[]) {
  if (typeof window === "undefined") return;
  window.localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
}

// Subscribers for cheap reactivity across components
const subs = new Set<() => void>();
function notify() { subs.forEach((s) => s()); }

export function subscribe(fn: () => void) {
  subs.add(fn);
  return () => { subs.delete(fn); };
}


export function listSurveys(): Survey[] {
  return load();
}

export function getSurvey(id: string): Survey | undefined {
  return load().find((s) => s.id === id);
}

export function upsertSurvey(survey: Survey) {
  const list = load();
  const i = list.findIndex((s) => s.id === survey.id);
  if (i === -1) list.unshift(survey); else list[i] = survey;
  save(list);
  notify();
}

export function deleteSurvey(id: string) {
  save(load().filter((s) => s.id !== id));
  notify();
}

export function createDraft(): Survey {
  const id = "s-" + Math.random().toString(36).slice(2, 8);
  const survey: Survey = {
    id,
    name: "Nova pesquisa",
    objective: "",
    audience: "",
    status: "rascunho",
    createdAt: new Date().toISOString(),
    goal: 100,
    questions: [],
    responses: [],
    chat: [
      { role: "assistant", text: "Olá! Sou seu assistente de pesquisa. Vou te ajudar a criar uma pesquisa eficaz em poucos minutos. Para começar, qual é o objetivo dessa pesquisa?" },
    ],
  };
  upsertSurvey(survey);
  return survey;
}

export function formatDate(iso: string) {
  return new Date(iso).toLocaleDateString("pt-BR", { day: "2-digit", month: "short", year: "numeric" });
}
