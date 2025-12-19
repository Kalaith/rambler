# Rambler Project Standards & Agent Instructions

This document synthesizes the core standards for the Rambler project, consolidating lessons from backend, frontend, and testing documentation.

## 🔑 Core Philosophy
- **Strict Typing**: Mandatory `strict_types=1` in PHP and strict mode in TypeScript with no `any` types.
- **Zero Errors**: Zero ESLint and PHP_CodeSniffer errors allowed.
- **Action/Service Pattern**: High-level logic belongs in Actions (backend) or custom Hooks (frontend).

---

## 🛠️ Backend Standards (PHP)
- **Tech Stack**: PHP 8.1+, Slim 4, PHP-DI, Eloquent ORM.
- **Authentication**: **USE JWT TOKENS (Direct implementation)**. Do NOT use Auth0 for this project. Utilize `firebase/php-jwt`.
- **AI Integration**: Use **Google AI Studio (Gemini 2.5 Flash)**.
    - **Endpoint**: `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent`
    - **Method**: POST
    - **Headers**: `Content-Type: application/json`, `x-goog-api-key: YOUR_API_KEY`
    - **Payload Structure**:
    ```json
    {
      "contents": [{ "parts": [{ "text": "Prompt here" }] }]
    }
    ```
- **Architecture**:
    - **Actions**: Contain core business processes (e.g., `CreateUserAction.php`).
    - **Thin Controllers**: Handle requests and return responses; no logic or direct DB calls.
    - **Repositories**: Handle all data persistence using Eloquent models.
    - **Services**: For complex cross-action logic or external integrations.
- **Structure**: PSR-12 compliance. Classes/Interfaces in PascalCase.

---

## 🎨 Frontend Standards (React/TypeScript)
- **Tech Stack**: React 19, TypeScript, Vite, Tailwind CSS, Zustand (state), Framer Motion (animation).
- **TypeScript Export Rule**: 
    - **Class mandatory** for runtime exports (e.g., `ApiResponse`, `ApiError`).
    - **Interfaces/Types** for type-only definitions (stripped during compilation).
- **Structure**:
    - `src/api`: Centralized API calls.
    - `src/components/ui`: Generic presentational components.
    - `src/hooks`: Encapsulated stateful behavior (logic).
    - `src/stores`: Single source of truth via Zustand.
    - `src/types`: Application-wide type definitions.
- **Naming**:
    - Components: `PascalCase.tsx`
    - Hooks: `useCamelCase.ts`
    - Assets: `kebab-case.ext`

---

## 🧪 Testing Standards
- **Frontend**: Vitest + React Testing Library.
    - Test behavior, not implementation.
    - Minimum **80% coverage** for components and hooks.
- **Backend**: PHPUnit.
    - Focus on Actions and Services.
    - Minimum **70% coverage**.
- **Mocking**: Always mock external dependencies (API, Repositories).

---

## ❌ Explicit Prohibitions
- No `any` types in TypeScript.
- No business logic in Controllers or direct Component state (if reusable).
- No direct database queries in Controllers.
- No missing error handling.
- No Auth0 (for this project).
