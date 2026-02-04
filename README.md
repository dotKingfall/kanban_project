# kanban_project
Plataforma centralizada para gestão do fluxo de trabalho e atendimento, permitindo o rastreio em tempo real de demandas por cliente e a otimização da entrega através de visualização Kanban.

## Dúvidas e Premissas
Pensei em criar um sistema completo onde é possível administrar e ter uma visão completa de todos os clientes ao qual o serviço é prestado. Cada demanda possui um cliente em esepcífico e é possível de saber se o cliente precisou pagar por ela ou não. Também pensei na importância de sinalizar de maneira visual quando uma demanda estivesse retornando ao ciclo de desenvolvimento.
O cliente também deveria ser capaz de pesquisar as informações mais importantes e utilizar uma ferramenta de kanban similar ao clickup para organizar suas tarefas e prazos, com a segurança da autenticação, para garantir que terceiros não tenham acesso a seus dados snesíveis.

Dúvidas:
Como é o melhor jeito de aprimorar a experiência do usuário com o tempo que tenho?
Quais funcionalidades eu mais gostaria de ter aousar o kanban todos os dias?
O que signifca cobrado do cliente?
Quais funcionalidades extras terão mais impacto no uso do cliente no dia a dia?
Quais endpoints posso adicionar agora para quando precisar expandir o escoo da aplicação?

## Requisitos
Antes de iniciar, certifique-se de que seu ambiente atende às versões abaixo:

- **Node.js (v24+):** `node -v`
- **PHP (v8.4+):** `php -v`
- **PostgreSQL (17):** `psql --version`
- **Composer:** `composer --version`
- **Git:** `git --version`

## Como rodar o Backend (Laravel)

Siga os passos abaixo para configurar o ambiente de desenvolvimento.
### 1. Clonar o Repositório

```bash
git clone git@github.com:dotKingfall/kanban_project.git
cd kanban_backend
```
### 2. Instalar Dependências

Use o Composer para instalar as bibliotecas do PHP:

```bash
composer install
```
### 3. Configuração do Ambiente

Crie o arquivo `.env` a partir do exemplo e gere a chave da aplicação:

```bash
cp .env.example .env
php artisan key:generate
```
### 4. Configurar o Banco de Dados

Abra o arquivo `.env` e configure as credenciais do seu **PostgreSQL 17**:

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=nome_do_seu_banco
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```
### 5. Migrações e Seeds

```bash
php artisan migrate --seed
```

### 6. Iniciar o Servidor

Com tudo configurado, suba o servidor local:

```bash
php artisan serve
```

O backend estará disponível em: `http://localhost:8000`

## Como rodar o Frontend (Quasar/Vue)

Após subir o backend, siga os passos abaixo para iniciar a interface.
### 1. Acessar a pasta do Frontend

Se você estiver na raiz do repositório:

```bash
cd kanban_frontend
```
### 2. Instalar Dependências

Use o NPM para instalar os pacotes do Node:

```bash
npm install
```
### 3. Iniciar o Servidor de Desenvolvimento

Rode o comando do Quasar para subir o projeto com _Hot Reload_:

```bash
npx quasar dev
## ou
npm run dev
```

**Caso de erro de cache rodar o comando abaixo na raiz do kanban_backend**
`mkdir -p bootstrap/cache`

## Usuários do sistema
 email: test1@test.com
 senha: password

 email:test2@test.com
 senha: 123123

## Endpoints da API

Todas as rotas (exceto `/login`) exigem autenticação via **Bearer Token** (Laravel Sanctum).
###  Autenticação

|**Método**|**Endpoint**|**Descrição**|
|---|---|---|
|`POST`|`/api/login`|Autentica o usuário e retorna o token|
|`POST`|`/api/logout`|Revoga o token de acesso|
|`GET`|`/api/user`|Retorna os dados do usuário logado|

###  Clientes (`Clients`)

| **Método** | **Endpoint**                 | **Descrição**                                          |
| ---------- | ---------------------------- | ------------------------------------------------------ |
| `GET`      | `/api/clients`               | Lista clientes (com suporte a query param `client_id`) |
| `POST`     | `/api/clients`               | Cadastra um novo cliente                               |
| `PUT`      | `/api/clients/{id}`          | Atualiza dados de um cliente específico                |
| `DELETE`   | `/api/clients/{id}`          | Remove um ou mais clientes                             |
| `GET`      | `/api/reports/clients`       | Retorna relatórios/estatísticas de clientes            |
| `POST`     | `/api/kanban-columns/update` | Atualiza a estrutura de colunas do cliente             |

### Demandas (`Demands`)

|**Método**|**Endpoint**|**Descrição**|
|---|---|---|
|`GET`|`/api/demands`|Lista todas as demandas|
|`POST`|`/api/demands`|Cria uma nova demanda|
|`PATCH`|`/api/demands/{id}`|Atualização parcial da demanda|
|`PATCH`|`/api/demands/{id}/status`|Altera o status (coluna) de uma demanda|
|`PATCH`|`/api/demands/{id}/position`|Altera a ordem da demanda dentro da coluna|
|`POST`|`/api/demands/reorder`|Reordenação em massa (drag & drop)|
|`DELETE`|`/api/demands/{demand}`|Remove uma demanda específica|

### Colunas do Kanban

|**Método**|**Endpoint**|**Descrição**|
|---|---|---|
|`PATCH`|`/api/kanban_column`|Atualiza nome ou propriedades da coluna|
|`DELETE`|`/api/kanban_column`|Remove uma coluna do kanban|

### Auxiliares (Lookups)

| **Método** | **Endpoint**       | **Descrição**                                         |
| ---------- | ------------------ | ----------------------------------------------------- |
| `GET`      | `/api/priorities`  | Lista as prioridades disponíveis (Alta, Média, Baixa) |
| `GET`      | `/api/departments` | Lista os departamentos cadastrados                    |

## Funcionalidades do Sistema

### Gestão de Clientes

- **Multi-tenant por Cliente:** Cada cliente possui seu próprio conjunto de demandas e configurações de colunas.
- **Estrutura Dinâmica:** Possibilidade de definir e atualizar quais colunas (status) cada cliente utiliza no seu fluxo.
- **Relatórios e Dashboards:** Área dedicada para análise de performance e volume de demandas por cliente.
### Fluxo Kanban (Drag & Drop)

- **Interface Intuitiva:** Movimentação de demandas entre colunas através de arrastar e soltar.
- **Ordenação Customizada:** Controle total sobre a posição das demandas dentro de uma mesma coluna.
- **Rastreio em Tempo Real:** Atualização imediata do status da demanda (Pendente, Em Andamento, Concluído, etc).
### Gestão de Demandas

- **Priorização:** Classificação de tarefas por níveis de urgência (Alta, Média, Baixa).
- **Categorização por Departamento:** Atribuição de demandas a setores específicos para melhor organização interna.
### 🔐 Segurança e Acesso

- **Autenticação Robusta:** Sistema protegido por tokens (Laravel Sanctum).
- **Isolamento de Dados:** Usuários autenticados acessam apenas as informações vinculadas ao seu perfil.

## Funcionalidades Extras
- Autenticação do usuário
- Drag and drop no Kanban
- Reordenar e esconder colunas
- Gerar PDF na tela de relatórios
- Pesquisa de clientes por nome ou email
- Tela de demandas globais
- Redirecionamento para demanda específica do cliente na tela de demandas gerais