# Katra - AI Workflow Engine

**By [DevOption](https://devoption.io)**

Katra is an Agentic Workflow Management System that allows you to automate ANY tasks by embedding AI agents into CI/CD-style pipelines. Named after the Vulcan concept of transferring one's consciousness, Katra enables experts to transfer their knowledge and decision-making patterns into autonomous agents.

## Features

- **AI Agent Management** - Create specialized AI agents with custom roles, prompts, and tools
- **Workflow Orchestration** - Build complex workflows with series, parallel, or DAG execution modes
- **Multi-Provider Support** - Works with OpenAI, Anthropic, Google, Ollama, and more
- **Flexible Execution** - Run workflows in Laravel queues, Docker containers, or Kubernetes
- **Context Sharing** - Agents can share knowledge and data across workflow steps
- **MCP Integration** - Extensible tool system with marketplace support
- **Human-in-the-Loop** - Add approval gates and manual interventions when needed
- **Beautiful UI** - Nord-themed interface with light/dark mode support

## Tech Stack

- Laravel 12
- Livewire 3 + Alpine.js
- Tailwind CSS v4 (Nord color palette)
- Pest v4 for testing
- SQLite (dev) / PostgreSQL (production)

## Installation

```bash
# Clone the repository
git clone https://git.devoption.io/katra/katra.git
cd katra

# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Run migrations and seed test users
php artisan migrate --seed

# Build assets
npm run build

# Start the development server
php artisan serve
```

## Default Users

After seeding, you can login with:

- **Admin**: admin@katra.test / password
- **User**: user@katra.test / password

## Development

```bash
# Run tests
php artisan test

# Format code
vendor/bin/pint

# Watch assets
npm run dev
```

## Vision

Katra aims to make expert knowledge scalable by allowing anyone to:
- Transfer expertise into AI agents via system prompts
- Automate complex workflows with minimal HITL steps
- Scale operations from small teams to enterprises
- Empower non-profits to serve more people

## Contributing

Contributions are welcome! Please see our contributing guidelines (coming soon).

## Security

If you discover a security vulnerability, please email security@devoption.io.

## License

Katra is open-source software licensed under the [MIT license](LICENSE).

---

**Made with ❤️ by DevOption**
