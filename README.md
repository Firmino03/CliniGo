<p align="center">
  <img src="docs/img/logo.png" alt="CliniGo" width="120">
</p>

<h1 align="center">CliniGo</h1>

<p align="center">
  <img src="docs/img/logo.png" alt="CliniGo" width="120">
</p>

<h1 align="center">CliniGo</h1>

Sistema de gestão ambulatorial para clínicas de pequeno porte. Projeto de portfólio que simula uma arquitetura real de empresa: uma **API REST em Laravel** integrada a um **serviço Java independente**.

## O que faz

- Autenticação por token, com papéis: admin, recepção e médico
- Cadastro de pacientes, médicos e especialidades
- Prontuário por consulta, com acesso restrito ao médico responsável
- **API REST versionada e documentada (Laravel)**
- **Agendamento de consultas com validação de conflito de horário (serviço Java)**
- **Consulta de horários livres por médico (serviço Java)**
- **Relatórios em PDF (serviço Java)**

## Stack

| Camada | Tecnologia |
|---|---|
| API REST, autenticação e cadastros | Laravel (PHP) + Sanctum |
| Serviço de agenda e relatórios | Java puro + JDBC + OpenPDF |
| Banco de dados | MySQL |
| Infra | Docker Compose (planejado) |

## Arquitetura

```mermaid
flowchart LR
    C["Cliente<br/>(frontend, Postman, curl)"] -->|"API REST /api/v1<br/>Bearer token"| L["Laravel<br/>auth, cadastros, prontuário"]
    L -->|"HTTP/JSON interno<br/>token de serviço"| J["Java<br/>agenda, conflito de horário, PDF"]
    L --> DB[(MySQL)]
    J --> DB
```

Existem **duas APIs REST**, com papéis diferentes:

| | API do Laravel | API do Java |
|---|---|---|
| Exposição | Pública (para clientes) | Interna (só o Laravel acessa) |
| Autenticação | Token do usuário (Sanctum) | Token de serviço compartilhado |
| Responsabilidade | Autenticação, permissões por papel, CRUDs, prontuário | Agendamento, regra de conflito, horários livres, PDFs |
| Tabelas que escreve | `users`, `pacientes`, `medicos`, `especialidades`, `prontuarios` | `consultas`, `relatorios_pdf` |

O cliente conversa **somente com o Laravel**. Quando o pedido envolve agenda ou PDF, o Laravel valida o usuário e o papel dele e repassa a chamada ao serviço Java.

## API REST

> **Status:** em desenvolvimento. O contrato abaixo é o planejado e será ajustado conforme a implementação.

### Por que uma API REST

A API é o contrato entre o backend e qualquer cliente. Com ela, o mesmo backend atende um frontend web, um app ou um teste via `curl`, sem mudar nada no servidor. Cada recurso (paciente, consulta, prontuário) tem uma URL própria, e o verbo HTTP diz a ação: `GET` lê, `POST` cria, `PUT` atualiza, `DELETE` remove. O servidor não guarda sessão: cada requisição carrega seu próprio token.

### Convenções

- **Versionamento:** todas as rotas começam com `/api/v1`, para evoluir o contrato sem quebrar clientes antigos.
- **Autenticação:** `Authorization: Bearer <token>`, obtido em `POST /api/v1/login` (Laravel Sanctum).
- **Autorização:** Policies do Laravel aplicam as regras por papel. Exemplo: o médico só acessa prontuários e pacientes que atendeu.
- **Validação:** Form Requests validam a entrada, inclusive o dígito verificador do CPF.
- **Saída:** API Resources controlam o JSON devolvido, sem expor colunas desnecessárias.

### Códigos de resposta

| Código | Significado |
|---|---|
| 200 / 201 | Sucesso / recurso criado |
| 401 | Não autenticado (token ausente ou inválido) |
| 403 | Autenticado, mas sem permissão para o recurso |
| 404 | Recurso não encontrado |
| 409 | Conflito (ex.: horário já ocupado) |
| 422 | Dados inválidos (erros de validação) |

### Endpoints planejados

| Método | Rota | Descrição | Papéis |
|---|---|---|---|
| POST | `/api/v1/login` | Gera o token de acesso | Público |
| POST | `/api/v1/logout` | Revoga o token atual | Autenticado |
| GET, POST | `/api/v1/pacientes` | Lista e cadastra pacientes | Admin, recepção |
| GET, PUT | `/api/v1/pacientes/{id}` | Consulta e atualiza um paciente | Admin, recepção |
| GET | `/api/v1/medicos` | Lista médicos | Autenticado |
| GET | `/api/v1/especialidades` | Lista especialidades | Autenticado |
| GET | `/api/v1/medicos/{id}/horarios-livres` | Horários livres do médico *(Java)* | Admin, recepção |
| POST | `/api/v1/consultas` | Agenda uma consulta *(Java)* | Admin, recepção |
| PATCH | `/api/v1/consultas/{id}/status` | Cancela, realiza ou marca falta *(Java)* | Admin, recepção, médico |
| GET, POST | `/api/v1/prontuarios` | Lista e cria prontuários | Médico responsável |
| POST | `/api/v1/relatorios/consultas` | Gera relatório em PDF *(Java)* | Admin |

### Exemplo: agendar uma consulta

Requisição:

```http
POST /api/v1/consultas
Authorization: Bearer <token>
Content-Type: application/json

{
  "paciente_id": 12,
  "medico_id": 3,
  "inicio": "2026-11-10 09:00:00",
  "fim": "2026-11-10 09:30:00"
}
```

Resposta de sucesso (`201`):

```json
{
  "data": {
    "id": 87,
    "paciente_id": 12,
    "medico_id": 3,
    "inicio": "2026-11-10 09:00:00",
    "fim": "2026-11-10 09:30:00",
    "status": "agendada"
  }
}
```

Resposta quando o horário já está ocupado (`409`):

```json
{
  "message": "O médico já possui uma consulta neste horário."
}
```

### Fluxo de uma chamada

1. O cliente envia a requisição ao Laravel com o token do usuário.
2. O Laravel autentica o token e checa se o papel do usuário permite a ação.
3. Para agenda e PDF, o Laravel repassa a chamada ao Java por HTTP, com um token de serviço.
4. O Java valida o conflito de horário dentro de uma transação, grava em `consultas` e responde.
5. O Laravel devolve a resposta ao cliente.

## Decisões de arquitetura

- **Schema versionado só no Laravel.** As migrations criam todas as tabelas, inclusive `consultas`. O Java usa o schema, não o altera.
- **Banco compartilhado.** Simplifica o projeto, mas acopla os serviços (`consultas` tem chaves estrangeiras para tabelas do Laravel). Em produção real, cada serviço teria seu próprio banco.
- **Conflito de horário tratado no Java**, dentro de uma transação com bloqueio, para evitar dois agendamentos simultâneos no mesmo horário.
- **Um único ponto de entrada.** O Java não é exposto: toda autenticação e autorização acontece no Laravel.

## Diagrama de classes (domínio)

Visão orientada a objetos do domínio. Cada classe é um model Eloquent em `laravel-app/app/Models`. As chaves estrangeiras (`paciente_id`, `medico_id` etc.) não aparecem como atributos: elas viram as **associações** entre as classes, com a multiplicidade em cada ponta. Os métodos listados são os de relacionamento que existem no código.

```mermaid
classDiagram
    direction LR

    class User {
        +int id
        +string name
        +string email
        -string password
        +Role role
        +medico() Medico
        +relatoriosPdf() Collection~RelatorioPdf~
    }
    class Especialidade {
        +int id
        +string nome
        +medicos() Collection~Medico~
    }
    class Medico {
        +int id
        +string crm
        +especialidade() Especialidade
    }
    class Paciente {
        +int id
        +string nome
        +string cpf
        +date data_nascimento
        +string telefone
        +consultas() Collection~Consulta~
        +prontuarios() Collection~Prontuario~
    }
    class Consulta {
        +int id
        +datetime inicio
        +datetime fim
        +StatusConsulta status
        +paciente() Paciente
        +medico() Medico
        +prontuario() Prontuario
    }
    class Prontuario {
        +int id
        +text anotacoes
        +consulta() Consulta
        +paciente() Paciente
        +medico() Medico
    }
    class RelatorioPdf {
        +int id
        +string tipo
        +json filtros
        +string arquivo_path
        +datetime gerado_em
        +user() User
    }
    class Role {
        <<enumeration>>
        admin
        recepcao
        medico
    }
    class StatusConsulta {
        <<enumeration>>
        agendada
        realizada
        cancelada
        faltou
    }

    User "1" -- "0..1" Medico : pode ser
    Especialidade "1" -- "0..*" Medico : possui
    Paciente "1" -- "0..*" Consulta : agenda
    Medico "1" -- "0..*" Consulta : atende
    Consulta "1" -- "0..1" Prontuario : gera
    Paciente "1" -- "0..*" Prontuario : possui
    Medico "1" -- "0..*" Prontuario : escreve
    User "1" -- "0..*" RelatorioPdf : solicita
    User ..> Role
    Consulta ..> StatusConsulta
```

Como ler o diagrama:

- Um **Paciente** pode ter várias **Consultas**, e cada **Consulta** é de um só paciente e um só médico.
- Cada **Consulta** gera no máximo um **Prontuario** (por isso `0..1`). O prontuário também guarda o paciente e o médico da consulta.
- **Medico** não tem nome nem e-mail: esses dados ficam no **User** ligado a ele (`0..1`, porque nem todo usuário é médico).
- `Role` e `StatusConsulta` são colunas `enum` no banco. Estão desenhadas como enumeração só para facilitar a leitura.

## Diagrama do banco

```mermaid
erDiagram
    USERS ||--o| MEDICOS : "pode ser"
    ESPECIALIDADES ||--o{ MEDICOS : "possui"
    PACIENTES ||--o{ CONSULTAS : "agenda"
    MEDICOS ||--o{ CONSULTAS : "atende"
    CONSULTAS ||--o| PRONTUARIOS : "gera"
    MEDICOS ||--o{ PRONTUARIOS : "escreve"
    PACIENTES ||--o{ PRONTUARIOS : "possui"
    USERS ||--o{ RELATORIOS_PDF : "solicita"

    USERS {
        bigint id PK
        string name
        string email UK
        string password
        enum role "admin|recepcao|medico"
        timestamps created_updated
    }
    ESPECIALIDADES {
        bigint id PK
        string nome UK
    }
    MEDICOS {
        bigint id PK
        bigint user_id FK
        bigint especialidade_id FK
        string crm UK
    }
    PACIENTES {
        bigint id PK
        string nome
        string cpf UK
        date data_nascimento
        string telefone
        timestamps created_updated
    }
    CONSULTAS {
        bigint id PK
        bigint paciente_id FK
        bigint medico_id FK
        datetime inicio
        datetime fim
        enum status "agendada|realizada|cancelada|faltou"
        timestamps created_updated
    }
    PRONTUARIOS {
        bigint id PK
        bigint consulta_id FK
        bigint paciente_id FK
        bigint medico_id FK
        text anotacoes
        timestamps created_updated
    }
    RELATORIOS_PDF {
        bigint id PK
        bigint user_id FK
        string tipo
        json filtros
        string arquivo_path
        datetime gerado_em
    }
```

## Factories e seeders (criação de objetos)

Factories e seeders são o jeito do projeto de criar vários objetos de uma vez. A factory define como é **um** objeto com dados falsos (Faker). O seeder decide **quantos** objetos criar e em que ordem.

| Classe | Papel | O que cria |
|---|---|---|
| `PacienteFactory` | Factory | 1 `Paciente` com nome, CPF válido, data de nascimento e telefone |
| `UserFactory` | Factory | 1 `User` com nome, e-mail e senha (`password`) |
| `MedicoFactory` | Factory | 1 `Medico` com CRM e uma especialidade sorteada. Cria também o `User` dele, com `role = medico` |
| `EspecialidadeSeeder` | Seeder | As 6 especialidades fixas (Clínica Geral, Cardiologia, Dermatologia, Pediatria, Ortopedia e Ginecologia) |
| `DatabaseSeeder` | Seeder | Chama o `EspecialidadeSeeder`, cria o admin (`admin@clinigo.test`), **30 pacientes** e **10 médicos** |

A ordem importa: o `MedicoFactory` sorteia uma especialidade que já existe, então o `EspecialidadeSeeder` precisa rodar antes. O `DatabaseSeeder` já faz isso.

```bash
php artisan migrate:fresh --seed
```

## Status

- [x] Base Laravel + MySQL
- [x] Migrations de todas as tabelas
- [x] Models e relacionamentos (Eloquent)
- [x] Factories e seeders
- [x] Autenticação por token (Sanctum) e permissões por papel
- [ ] API REST do Laravel: pacientes, médicos, especialidades, prontuários
- [ ] Documentação da API
- [ ] Serviço Java: agendamento com validação de conflito
- [ ] Serviço Java: relatórios em PDF
- [ ] Integração Laravel → Java
- [ ] Docker Compose

## Aviso

Todos os dados são fictícios (Faker). Este é um MVP de estudo e não deve ser usado com dados reais de pacientes. Dados pessoais e de saúde são sensíveis pela LGPD: num sistema real, esses campos teriam criptografia e controle de acesso rigoroso.