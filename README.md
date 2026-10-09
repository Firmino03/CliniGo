# CiniGo
<img width="220" height="220" alt="image" src="https://github.com/user-attachments/assets/7940b77b-b824-4e75-9dd3-81f4e08e3310" />

Sistema de gestão ambulatorial para clínicas de pequeno porte. Projeto de portfólio que simula uma arquitetura real de empresa.

## O que faz

- Autenticação com papéis: admin, recepção e médico
- Cadastro de pacientes, médicos e especialidades
- Agendamento de consultas com validação de conflito de horário
- Prontuário por consulta, com acesso restrito ao médico responsável
- Relatórios em PDF gerados por um serviço Java independente

## Stack

| Camada | Tecnologia |
|---|---|
| Aplicação principal | Laravel (PHP) |
| Banco de dados | MySQL |
| Serviço de relatórios | Java puro + JDBC + OpenPDF |
| Infra | Docker Compose |

## Aviso

Todos os dados são fictícios (Faker). Este é um MVP de estudo, não deve ser usado com dados reais de pacientes.

# Diagrama do banco

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
