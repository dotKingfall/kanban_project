// --- Interfaces ---

export interface User {
  id: number;
  first_name: string;
  last_name: string;
  email: string;
  email_verified_at?: string | null;
  reverse_filter: boolean;
  created_at?: string;
  updated_at?: string;
}

export interface Priority {
  id: number;
  name: string;
  value: number;
  created_at?: string;
  updated_at?: string;
}

export interface Department {
  id: number;
  name: string;
  created_at?: string;
  updated_at?: string;
}

export interface DemandFilterType {
  id: number;
  name: string;
  created_at?: string;
  updated_at?: string;
}

export interface ClientFilterType {
  id: number;
  name: string;
  created_at?: string;
  updated_at?: string;
}

export interface Client {
  id: number;
  nome: string;
  email?: string | null;
  whatsapp?: string | null;
  avisar_por_email: boolean;
  avisar_por_whatsapp: boolean;
  observacao?: string | null;
  reverse_filter: boolean;
  global_filter_id?: number | null;
  created_at?: string;
  updated_at?: string;
  global_filter?: DemandFilterType;
  user_id: number;
}

export interface KanbanColumn {
  id: number;
  client_id: number;
  name: string;
  position: number;
  is_fixed: boolean;
  is_hidden: boolean;
  reverse_filter: boolean;
  created_at?: string;
  updated_at?: string;
}

export interface Attachment {
  name: string;
  url: string;
  type?: string;
}

export interface Demand {
  id: number;
  cliente: number; // Foreign Key to Client
  position_in_column: number;
  titulo: string;
  descricao_detalhada?: string | null;
  responsavel?: string | null;
  quem_deve_testar?: string | null;
  anexos?: Attachment[] | null; // JSON field
  cobrada_do_cliente: boolean;
  flag_returned: boolean;
  tempo_estimado: number;
  tempo_gasto: number;
  data_cadastro: string; // Timestamp
  prioridade?: string | null;
  setor?: string | null;
  status?: string | null;

  // Foreign Keys
  priority_table_id?: number | null;
  department_table_id?: number | null;
  kanban_column_id: number;

  created_at?: string;
  updated_at?: string;

  // Relations
  client?: Client;
  kanban_column?: KanbanColumn;
  priority_obj?: Priority;
  department_obj?: Department;
}


// --- Factory Functions (For creating new objects) ---
export function makeEmptyClient(): Omit<Client, 'id' | 'created_at' | 'updated_at'> {
  return {
    nome: '',
    email: '',
    whatsapp: '',
    avisar_por_email: false,
    avisar_por_whatsapp: false,
    observacao: '',
    reverse_filter: false,
    global_filter_id: null,
    user_id: 0,
  };
}

export function makeEmptyDemand(defaultColumnId: number, defaultClientId: number): Omit<Demand, 'id' | 'created_at' | 'updated_at' | 'data_cadastro'> {
  return {
    cliente: defaultClientId,
    kanban_column_id: defaultColumnId,
    position_in_column: 0,
    titulo: '',
    descricao_detalhada: '',
    responsavel: '',
    quem_deve_testar: '',
    anexos: null,
    cobrada_do_cliente: false,
    flag_returned: false,
    tempo_estimado: 0,
    tempo_gasto: 0,
    prioridade: null,
    setor: null,
    status: null,
    priority_table_id: null,
    department_table_id: null,
  };
}
