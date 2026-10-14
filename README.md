<p align="center">
  <img src="docs/img/logo.png" alt="CliniGo" width="120">
</p>

<h1 align="center">CliniGo</h1>

Sistema de gestão ambulatorial para clínicas de pequeno porte. Projeto de portfólio que simula uma arquitetura real de empresa: um portal em Laravel integrado a um serviço Java independente.

## O que faz

- Autenticação com papéis: admin, recepção e médico
- Cadastro de pacientes, médicos e especialidades
- Prontuário por consulta, com acesso restrito ao médico responsável
- **Agendamento de consultas com validação de conflito de horário (serviço Java)**
- **Consulta de horários livres por médico (serviço Java)**
- **Relatórios em PDF (serviço Java)**

## Stack

| Camada | Tecnologia |
|---|---|
| Portal web, autenticação e cadastros | Laravel (PHP) |
| Serviço de agenda e relatórios | Java puro + JDBC + OpenPDF |
| Banco de dados | MySQL |
| Infra | Docker Compose |

## Arquitetura

```mermaid
flowchart LR
    U[Usuário] --> L["Laravel<br/>login, cadastros, prontuário"]
    L -->|HTTP/JSON + token| J["Java<br/>agenda, conflito de horário, PDF"]
    L --> DB[(MySQL)]
    J --> DB
```

Cada serviço é dono do seu domínio:

| | Laravel | Java |
|---|---|---|
| Tabelas | `users`, `pacientes`, `medicos`, `especialidades`, `prontuarios` | `consultas`, `relatorios_pdf` |
| Responsabilidade | Autenticação, permissões, telas, CRUDs | Agendamento, regra de conflito, horários livres, PDFs |

O Laravel autentica o usuário e chama o serviço Java por HTTP. O Java não é exposto externamente.

## Decisões de arquitetura

- **Schema versionado só no Laravel.** As migrations criam todas as tabelas, inclusive `consultas`. O Java usa o schema, não o altera.
- **Banco compartilhado.** Simplifica o projeto, mas acopla os serviços (`consultas` tem chaves estrangeiras para tabelas do Laravel). Em produção real, cada serviço teria seu próprio banco.
- **Conflito de horário tratado no Java**, dentro de uma transação com bloqueio, para evitar dois agendamentos simultâneos no mesmo horário.

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


## Aviso

Todos os dados são fictícios (Faker). Este é um MVP de estudo e não deve ser usado com dados reais de pacientes. Dados pessoais e de saúde são sensíveis pela LGPD: num sistema real, esses campos teriam criptografia e controle de acesso rigoroso.