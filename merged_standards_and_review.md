# WebHatchery Full-Stack Standards and Code Review

This document combines the comprehensive standards for frontend and backend development, along with detailed code review guidelines and scoring criteria for full-stack applications.

## Table of Contents

1. [Frontend Standards](#frontend-standards)
2. [Backend Standards](#backend-standards)
3. [Code Review Guidelines](#code-review-guidelines)

---

# Frontend Standards

This section covers frontend development standards for React/TypeScript projects within our ecosystem, with specific focus on clean code principles and game application patterns.

## 📋 Frontend Standards (React/TypeScript)

### Core Technologies

*   **Framework**: React (latest stable version)
*   **Language**: TypeScript (latest stable version)
    *   **Rationale**: Provides static type checking, improving code quality, readability, and reducing runtime errors.
*   **Build Tool**: Vite
    *   **Rationale**: Offers extremely fast cold start times, instant hot module replacement (HMR), and optimized production builds.
*   **Styling**: Tailwind CSS
    *   **Rationale**: Utility-first CSS framework for rapid UI development, consistent design, and highly optimized CSS bundles.
*   **Animation**: Framer Motion
    *   **Rationale**: A production-ready motion library for React, enabling smooth and performant animations and transitions.
*   **Routing**: React Router DOM
    *   **Rationale**: Standard solution for declarative routing in React applications.
*   **State Management**: Zustand
    *   **Rationale**: Lightweight, performant, and easy-to-use state management solution.
*   **Server State (Optional)**: React Query
    *   **Rationale**: For applications with significant backend interaction, React Query provides robust solutions for data fetching, caching, synchronization, and error handling.

## 🚨 Critical TypeScript Export Standards

**CRITICAL LESSON LEARNED**: TypeScript `interface` declarations are stripped during Vite compilation and don't exist at runtime, causing "does not provide an export named" errors.

### Correct Export Patterns

**✅ CORRECT: Use classes for runtime exports**
```typescript
// API client exports - these survive compilation
export class ApiResponse<T = any> {
  success!: boolean;
  data?: T;
  message?: string;
}

export class ApiError extends Error {
  constructor(
    message: string,
    public statusCode: number,
    public details?: any
  ) {
    super(message);
  }
}
```

**✅ CORRECT: Use type aliases for type-only imports**
```typescript
export type UserData = {
  id: string;
  username: string;
  email: string;
};

// Import with type-only import
import type { UserData } from './types';
import { ApiResponse } from './client'; // Class import works at runtime
```

**❌ WRONG: Interface exports that will be imported as values**
```typescript
// This will be stripped during compilation
export interface ApiResponse<T = any> {
  success: boolean;
  data?: T;
  error?: string;
}

// This will cause runtime error:
// "The requested module does not provide an export named 'ApiResponse'"
import { ApiResponse } from './client';
```

### Debugging Runtime Import Errors

When encountering "does not provide an export named" errors:

1. **Check compiled output**: Examine DevTools Sources tab to see what actually exists at runtime
2. **Understand compilation pipeline**: Vite/TypeScript strips type-only constructs
3. **Use browser DevTools**: Verify module exports in Network tab and console
4. **Test imports directly**: Use browser console to verify what's actually exported from modules

### Export Decision Matrix

| Use Case | Export Type | Reason |
|----------|-------------|---------|
| Runtime values (API responses, error classes) | `class` | Survives compilation, available at runtime |
| Type-only definitions | `type` or `interface` | Stripped at compile time, type-only imports |
| Utility functions | `function` | Available at runtime |
| Constants | `const` | Available at runtime |
| Enums | `enum` | Available at runtime (compiled to objects) |

## 📦 Required Dependencies

### Production Dependencies
```json
{
  "dependencies": {
    "react": "^18.2.0",
    "react-dom": "^18.2.0",
    "react-router-dom": "^6.8.0",
    "zustand": "^4.3.0",
    "framer-motion": "^10.0.0",
    "tailwindcss": "^3.2.0",
    "@heroicons/react": "^2.0.0",
    "axios": "^1.3.0"
  }
}
```

### Optional Dependencies (Add as needed)
```json
{
  "dependencies": {
    "@tanstack/react-query": "^4.20.0",
    "react-hook-form": "^7.40.0",
    "yup": "^0.32.0",
    "date-fns": "^2.29.0"
  }
}
```

### Development Dependencies
```json
{
  "devDependencies": {
    "@types/react": "^18.0.0",
    "@types/react-dom": "^18.0.0",
    "@typescript-eslint/eslint-plugin": "^5.50.0",
    "@typescript-eslint/parser": "^5.50.0",
    "@vitejs/plugin-react": "^3.0.0",
    "eslint": "^8.30.0",
    "eslint-plugin-react-hooks": "^4.6.0",
    "eslint-plugin-react-refresh": "^0.3.0",
    "jsdom": "^21.0.0",
    "prettier": "^2.8.0",
    "typescript": "^4.9.0",
    "vite": "^4.0.0",
    "vitest": "^0.28.0",
    "@testing-library/react": "^13.4.0",
    "@testing-library/jest-dom": "^5.16.0",
    "@testing-library/user-event": "^14.4.0"
  }
}
```

## 📜 Required Scripts (`package.json`)

```json
{
  "scripts": {
    "dev": "vite",
    "build": "tsc && vite build",
    "lint": "eslint . --ext ts,tsx --report-unused-disable-directives --max-warnings 0",
    "lint:fix": "eslint . --ext ts,tsx --fix",
    "preview": "vite preview",
    "test": "vitest",
    "test:ui": "vitest --ui",
    "type-check": "tsc --noEmit",
    "ci": "npm run lint && npm run type-check && npm run test && npm run build",
    "ci:quick": "npm run lint && npm run type-check && npm run test"
  }
}
```

**Key Scripts:**
- `npm run dev` - Development server
- `npm run build` - Production build
- `npm run ci` - Full CI pipeline (lint, type-check, test, build)
- `npm run ci:quick` - Quick validation (skip build)

### Project Structure

All React frontend projects **must** adhere to the following standardized directory structure. This promotes discoverability, modularity, and consistency.

```
src/
├── api/                # (Optional) API service definitions, client instances, and related types for backend interaction.
│                       # Use this for centralized API calls, e.g., `api/auth.ts`, `api/game.ts`.
├── components/         # Reusable React components.
│   ├── ui/             # Generic, presentational UI components (e.g., Button, Modal, Input, Card).
│   │                   # These components should be highly reusable and have minimal business logic.
│   ├── layout/         # Application layout components (e.g., TopNav, MainLayout, Sidebar).
│   │                   # These provide consistent navigation and page structure across the app.
│   ├── game/           # Game-specific components (e.g., DragonDisplay, UpgradeCard, MinionPanel, AdventurerList).
│   │                   # These components encapsulate game-specific UI and logic.
│   └── wizard/         # Multi-step form/wizard components for complex user flows.
├── hooks/              # Custom React hooks for encapsulating reusable logic and stateful behavior.
│                       # (e.g., `useGameLoop`, `useOfflineEarnings`, `useAuth`, `useFormValidation`).
├── stores/             # State management definitions using Zustand.
│                       # Each file in this directory should define a single Zustand store.
│                       # (e.g., `useGameStore.ts`, `usePlayerStore.ts`, `useSettingsStore.ts`).
├── types/              # Centralized TypeScript type definitions and interfaces.
│                       # This includes interfaces for API responses, game entities, component props, and global types.
│                       # (e.g., `game.d.ts`, `api.d.ts`, `components.d.ts`).
├── data/               # Static, immutable game data or configuration files.
│                       # (e.g., `treasures.ts`, `upgrades.ts`, `achievements.ts`, `npcs.ts`).
│                       # These files should export plain JavaScript objects/arrays.
├── utils/              # Utility functions and core game logic that are not tied to React components or hooks.
│                       # (e.g., calculation functions, data transformers, helper functions).
├── assets/             # Static assets like images, icons, fonts, and other media files.
│                       # (If not served from the `public/` directory).
├── styles/             # Global CSS files, Tailwind CSS configuration, and any custom base styles.
│                       # (e.g., `index.css`, `tailwind.css`).
├── App.tsx             # The main application component.
├── main.tsx            # Entry point for the React application (ReactDOM.render).
└── vite-env.d.ts       # Vite environment type definitions.
```

#### Directory Structure & Modularity

For larger applications, organize code by feature/domain rather than technical layer to improve scalability:

*   **Feature Folders**: Group related components, hooks, stores, and types together:
    ```
    src/
    ├── features/
    │   ├── auth/
    │   │   ├── components/
    │   │   ├── hooks/
    │   │   ├── stores/
    │   │   └── types/
    │   ├── game/
    │   │   ├── components/
    │   │   ├── hooks/
    │   │   ├── stores/
    │   │   └── types/
    │   └── settings/
    │       ├── components/
    │       ├── hooks/
    │       ├── stores/
    │       └── types/
    ├── shared/
    │   ├── components/
    │   ├── hooks/
    │   ├── stores/
    │   └── types/
    └── App.tsx
    ```
*   **Monorepo Setup**: For multi-team environments, use Yarn/NPM workspaces to create a monorepo where each feature or shared library is its own package. This allows parallel development and independent versioning.
*   **Shared Packages**: Extract common UI components and utilities into separate packages that can be published and reused across projects.

#### Shared Components & Design System

To promote consistency across multiple projects and teams:

*   **Design System**: Establish a shared component library using Storybook for documentation and testing. Publish it as an npm package or host it in a monorepo.
*   **Component Library**: Create reusable UI components (buttons, forms, modals) in a dedicated package that all projects can import.
*   **Storybook Documentation**: Document every shared component with usage examples, props, and variations.
*   **Versioning**: Use semantic versioning for the shared library to manage breaking changes.
*   **Consistency Enforcement**: Require all teams to use the shared components instead of creating duplicates.

Example shared library structure:
```
packages/ui-library/
├── src/
│   ├── components/
│   ├── stories/
│   └── index.ts
├── package.json
└── .storybook/
```

#### Component Organization Rules

> **⚠️ CRITICAL**: Follow these rules when creating new components to avoid refactoring.

**File Size Limits:**
*   **Maximum 300 lines per component file**. If a component exceeds this, extract sub-components.
*   **Pages should orchestrate, not implement**. Page components should compose smaller components, not contain all UI logic inline.

**No Inline Helper Components:**
*   ❌ **WRONG**: Defining `Section`, `Modal`, or other reusable components at the bottom of a page file.
*   ✅ **CORRECT**: Extract to `components/ui/` (generic) or `components/[feature]/` (feature-specific).

```typescript
// ❌ WRONG: Helper component defined in page file
const MonsterMakerPage = () => { /* ... */ };
const Section = ({ title, children }) => <div>...</div>; // BAD - inline helper

// ✅ CORRECT: Import from dedicated component file
import CollapsibleSection from '../components/ui/CollapsibleSection';
```

**Utility Functions:**
*   ❌ **WRONG**: Utility functions defined at the bottom of component files.
*   ✅ **CORRECT**: Extract to `utils/[feature]Utils.ts` or `utils/helpers.ts`.

```typescript
// ❌ WRONG: Utility function in component file
const formatSpeeds = (speed) => { /* ... */ };
export default MonsterMakerPage;

// ✅ CORRECT: Import from utils
import { formatSpeeds } from '../utils/monsterUtils';
```

**No Barrel Exports (index.ts):**
*   ❌ **WRONG**: Creating `index.ts` files to re-export components.
*   ✅ **CORRECT**: Use direct imports to the specific component file.

```typescript
// ❌ WRONG: Barrel export
import { Modal, Button } from '../components/ui';

// ✅ CORRECT: Direct imports
import Modal from '../components/ui/Modal';
import Button from '../components/ui/Button';
```

**Feature Component Folders:**
*   Feature-specific components go in `components/[feature-name]/` (e.g., `components/monster-maker/`).
*   Generic reusable UI goes in `components/ui/`.
*   Layout components go in `components/layout/`.

### Data Flow & Storage

*   **Static Data**: Stored in `src/data/` as TypeScript files exporting plain objects/arrays. Loaded once at application startup or as needed.
*   **Client-Side Dynamic State**: Managed exclusively by Zustand stores (`src/stores/`). This is the single source of truth for the UI.
*   **Local Storage**: Used for persisting critical game state (e.g., player progress, settings) via Zustand's `persist` middleware.
*   **API Interaction**:
    *   Centralize API calls within the `src/api/` directory.
    *   Use Axios or Fetch API for HTTP requests.
    *   Handle loading, error, and success states in components, often facilitated by React Query if used.

### Authentication

For projects requiring user authentication, implement username/password authentication with JWT tokens:

*   **Token Storage**: Store JWT tokens in localStorage or sessionStorage. Never store sensitive data in cookies without proper security flags.
*   **API Client**: Create a centralized API client that automatically includes the JWT token in request headers:
    ```typescript
    import axios, { AxiosInstance } from 'axios';

    class ApiClient {
      private client: AxiosInstance;

      constructor() {
        this.client = axios.create({
          baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
          timeout: 10000,
        });

        // Request interceptor to add auth token
        this.client.interceptors.request.use((config) => {
          const token = localStorage.getItem('authToken');
          if (token) {
            config.headers.Authorization = `Bearer ${token}`;
          }
          return config;
        });

        // Response interceptor for error handling
        this.client.interceptors.response.use(
          (response) => response,
          (error) => {
            if (error.response?.status === 401) {
              // Handle unauthorized access
              localStorage.removeItem('authToken');
              window.location.href = '/login';
            }
            return Promise.reject(error);
          }
        );
      }

      get<T = any>(url: string): Promise<T> {
        return this.client.get(url).then(res => res.data);
      }

      post<T = any>(url: string, data?: any): Promise<T> {
        return this.client.post(url, data).then(res => res.data);
      }

      put<T = any>(url: string, data?: any): Promise<T> {
        return this.client.put(url, data).then(res => res.data);
      }

      delete<T = any>(url: string): Promise<T> {
        return this.client.delete(url).then(res => res.data);
      }
    }

    export const apiClient = new ApiClient();
    ```
*   **Auth Store**: Use Zustand to manage authentication state:
    ```typescript
    import { create } from 'zustand';
    import { persist } from 'zustand/middleware';

    interface User {
      id: string;
      username: string;
      email: string;
    }

    interface AuthState {
      user: User | null;
      token: string | null;
      isAuthenticated: boolean;
      login: (token: string, user: User) => void;
      logout: () => void;
    }

    export const useAuthStore = create<AuthState>()(
      persist(
        (set) => ({
          user: null,
          token: null,
          isAuthenticated: false,
          login: (token, user) => {
            localStorage.setItem('authToken', token);
            set({ user, token, isAuthenticated: true });
          },
          logout: () => {
            localStorage.removeItem('authToken');
            set({ user: null, token: null, isAuthenticated: false });
          },
        }),
        {
          name: 'auth-storage',
          partialize: (state) => ({ user: state.user, token: state.token }),
        }
      )
    );
    ```
*   **Protected Routes**: Implement route protection using React Router:
    ```typescript
    import { Navigate, useLocation } from 'react-router-dom';
    import { useAuthStore } from '../stores/useAuthStore';

    interface ProtectedRouteProps {
      children: React.ReactNode;
    }

    export const ProtectedRoute: React.FC<ProtectedRouteProps> = ({ children }) => {
      const { isAuthenticated } = useAuthStore();
      const location = useLocation();

      if (!isAuthenticated) {
        return <Navigate to="/login" state={{ from: location }} replace />;
      }

      return <>{children}</>;
    };
    ```
*   **Login/Register Forms**: Use form validation libraries like Formik or React Hook Form with Yup for schema validation.

### Component Standards
```typescript
// ✅ CORRECT: Functional component with proper typing
interface GameComponentProps {
  title: string;
  description?: string;
  isActive?: boolean;
}

export const GameComponent: React.FC<GameComponentProps> = ({
  title,
  description,
  isActive = false
}) => {
  const [localState, setLocalState] = useState<string>('');

  return (
    <div className={`p-4 rounded-lg ${isActive ? 'bg-blue-100' : 'bg-gray-100'}`}>
      <h2 className="text-xl font-bold">{title}</h2>
      {description && <p className="text-gray-600">{description}</p>}
      <input
        type="text"
        value={localState}
        onChange={(e) => setLocalState(e.target.value)}
        className="mt-2 p-2 border rounded"
      />
    </div>
  );
};

// ❌ WRONG: Class components, any types, inline styles
```

## 🛠️ Development Workflow

*   **Linting**: ESLint with TypeScript ESLint plugin.
    *   **Configuration**: Use a consistent `.eslintrc.js` across projects.
    *   **Enforcement**: Integrate linting into pre-commit hooks or CI/CD pipelines.
*   **Code Formatting**: Prettier (recommended, but not strictly enforced by this document).
*   **Testing**: Vitest with React Testing Library.
    *   **Configuration**: Use `vitest.config.ts` for test setup.
    *   **Coverage**: Aim for comprehensive test coverage for components and hooks.
*   **Build & Serve**: Use Vite scripts (`npm run dev`, `npm run build`, `npm run preview`).

## 🔄 CI/CD Configuration (`.github/workflows/ci.yml`)

```yaml
name: CI

on:
  pull_request:

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: actions/setup-node@v3
        with:
          node-version: 18
          cache: 'npm'
      - run: npm ci
      - run: npm run lint
      - run: npm run type-check
      - run: npm run test
      - run: npm run build
```

**Key Features:**
- ✅ Runs on PRs and pushes to main/master
- ✅ Node.js 18 LTS with dependency caching
- ✅ Full quality gate pipeline
- ✅ Build artifact verification
- ✅ Monorepo support (frontend subdirectory)

### CI/CD and Enforcement

To ensure consistency across all projects, implement the following CI/CD practices:
*   **Git Hooks**: Use Husky and lint-staged for pre-commit enforcement:
    ```bash
    npm install --save-dev husky lint-staged
    npx husky install
    npx husky add .husky/pre-commit "npx lint-staged"
    ```
*   **Pre-commit Hook**: Automatically run linting, formatting, and tests before commits.

## 📦 Required Configuration Files

### 1. TypeScript Configuration (`tsconfig.json`)

```json
{
  "compilerOptions": {
    "target": "ES2020",
    "useDefineForClassFields": true,
    "lib": ["ES2020", "DOM", "DOM.Iterable"],
    "module": "ESNext",
    "skipLibCheck": true,
    "moduleResolution": "bundler",
    "allowImportingTsExtensions": true,
    "resolveJsonModule": true,
    "isolatedModules": true,
    "noEmit": true,
    "jsx": "react-jsx",
    "strict": true,
    "noUnusedLocals": true,
    "noUnusedParameters": true,
    "noFallthroughCasesInSwitch": true,
    "noUncheckedIndexedAccess": true,
    "exactOptionalPropertyTypes": true
  },
  "include": ["src"],
  "references": [{ "path": "./tsconfig.node.json" }]
}
```

**Key Features:**
- ✅ Modern ES2020 target with proper DOM types
- ✅ Strictest TypeScript settings enabled
- ✅ React JSX support with new transform
- ✅ Comprehensive error catching

### 2. ESLint Configuration (`eslint.config.js`)

```javascript
import js from '@eslint/js'
import globals from 'globals'
import reactHooks from 'eslint-plugin-react-hooks'
import reactRefresh from 'eslint-plugin-react-refresh'
import tseslint from 'typescript-eslint'

export default tseslint.config(
  { ignores: ['dist', '*.config.ts', 'vitest.config.ts', 'vite.config.ts'] },
  {
    extends: [js.configs.recommended, ...tseslint.configs.recommended],
    files: ['**/*.{ts,tsx}'],
    languageOptions: {
      ecmaVersion: 2020,
      globals: globals.browser,
    },
    plugins: {
      'react-hooks': reactHooks,
      'react-refresh': reactRefresh,
    },
    rules: {
      ...reactHooks.configs.recommended.rules,
      'react-refresh/only-export-components': [
        'warn',
        { allowConstantExport: true },
      ],
      '@typescript-eslint/no-unused-vars': ['error', { argsIgnorePattern: '^_' }],
      '@typescript-eslint/no-explicit-any': 'error',
      '@typescript-eslint/explicit-function-return-type': 'off',
    },
  },
)
```

**Key Features:**
- ✅ WebHatchery naming conventions enforced
- ✅ Strict TypeScript rules (no `any`, no unused vars)
- ✅ React Hooks and React Refresh support
- ✅ Separate configuration for config files

### 3. Prettier Configuration (`prettier.config.js`)

```javascript
export default {
  semi: true,
  trailingComma: 'es5',
  singleQuote: true,
  printWidth: 80,
  tabWidth: 2,
  useTabs: false,
  bracketSameLine: false,
};
```

**Key Features:**
- ✅ Consistent code formatting across all files
- ✅ Single quotes for JS, double quotes for JSX
- ✅ 2-space indentation (industry standard)
- ✅ Trailing commas for cleaner diffs

### 4. Vitest Configuration (`vitest.config.ts`)

```typescript
import { defineConfig } from 'vitest/config';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: './src/test/setup.ts',
  },
});
```

### 5. Test Setup (`src/test/setup.ts`)

```typescript
import '@testing-library/jest-dom';
```

### Tooling Configuration

Provide shared configurations to ensure consistency:

*   **Shared ESLint Config**: Use the configuration above to enforce naming conventions and strict TypeScript rules.
*   **Shared Prettier Config**: Maintain consistent formatting across projects.
*   **Shared TypeScript Config**: The configuration above enables strict mode and comprehensive error checking.
*   **Automated Checks**: Use tools like `commitlint` for commit message standards and `stylelint` for CSS consistency.

### Naming Conventions

Follow these comprehensive naming rules for consistency:

*   **Components**: PascalCase (e.g., `GameBoard.tsx`, `UserProfile.tsx`)
*   **Pages**: PascalCase ending with 'Page' (e.g., `DashboardPage.tsx`)
*   **Hooks**: camelCase starting with 'use' (e.g., `useGameLogic.ts`)
*   **Stores**: camelCase ending with 'Store' (e.g., `gameStore.ts`)
*   **Types/Interfaces**: PascalCase (e.g., `GameState.ts`)
*   **Utils**: camelCase (e.g., `formatters.ts`)
*   **Constants**: UPPER_SNAKE_CASE for global constants (e.g., `MAX_LEVEL`, `API_BASE_URL`)
*   **Assets**: kebab-case for files (e.g., `dragon-icon.png`), camelCase for directories
*   **Tests**: Same name as the file being tested with `.test.tsx` suffix
*   **Enums**: PascalCase (e.g., `ItemRarity.Common`)

Enforce these rules via ESLint naming plugins and automated checks.

## 📚 Documentation Standards

*   **README.md**: Each project's `README.md` must provide a clear overview, setup instructions, and a summary of its architecture.
*   **Code Comments**: Use comments sparingly, primarily for explaining *why* a piece of code exists or for complex algorithms, rather than *what* it does.
*   **Type Definitions**: Leverage TypeScript interfaces and JSDoc comments for self-documenting code.
*   **API Documentation**: Document API endpoints and data structures clearly in the `src/api/` directory.

## 🧹 Clean Code Principles (React/TypeScript)

### 1. Meaningful Naming
```typescript
// ✅ CORRECT: Clear, descriptive names
interface UserProfileData {
  firstName: string;
  lastName: string;
  emailAddress: string;
}

const calculateUserExperiencePoints = (level: number): number => {
  return level * EXPERIENCE_MULTIPLIER;
};

const UserProfileCard: React.FC<UserProfileCardProps> = ({ userData }) => {
  // Implementation
};

// ❌ WRONG: Abbreviations, unclear names
interface UsrData {
  fn: string;
  ln: string;
  email: string;
}

const calc = (l: number): number => l * 100;
const UPC: React.FC = ({ data }) => { /* */ };
```

### 2. Single Responsibility Principle (SRP)
```typescript
// ✅ CORRECT: Each component has one responsibility
const UserAvatar: React.FC<{ imageUrl: string; size: 'sm' | 'md' | 'lg' }> = ({
  imageUrl,
  size
}) => (
  <img 
    src={imageUrl} 
    alt="User avatar" 
    className={`rounded-full ${size === 'sm' ? 'w-8 h-8' : size === 'md' ? 'w-12 h-12' : 'w-16 h-16'}`}
  />
);

const UserName: React.FC<{ firstName: string; lastName: string }> = ({
  firstName,
  lastName
}) => (
  <span className="font-semibold">{firstName} {lastName}</span>
);

const UserProfileCard: React.FC<UserProfileCardProps> = ({ user }) => (
  <div className="p-4 bg-white rounded-lg shadow">
    <UserAvatar imageUrl={user.avatarUrl} size="md" />
    <UserName firstName={user.firstName} lastName={user.lastName} />
  </div>
);

// ❌ WRONG: Single component doing everything
const UserCard: React.FC = ({ user }) => {
  // Avatar rendering logic
  const avatarSize = 'md';
  // ... (100+ lines)
};
```

### 3. Leverage TypeScript's Type System
```typescript
// ✅ CORRECT: Explicit types, no any
interface GameState {
  level: number;
  experience: number;
  health: number;
  inventory: Item[];
}

interface Item {
  id: string;
  name: string;
  type: ItemType;
  rarity: Rarity;
  stats: ItemStats;
}

type ItemType = 'weapon' | 'armor' | 'consumable' | 'misc';
type Rarity = 'common' | 'rare' | 'epic' | 'legendary';

const calculateItemValue = (item: Item): number => {
  const baseValue = getBaseValue(item.type);
  const rarityMultiplier = getRarityMultiplier(item.rarity);
  return baseValue * rarityMultiplier;
};

// ❌ WRONG: Using any, loose typing
const calcValue = (item: any): any => {
  return item.base * item.mult;
};

interface BadProps {
  data: any;
  callback: any;
}
```

### 4. Component Structure and Organization
```typescript
// ✅ CORRECT: Small, focused components
// components/game/PlayerStats.tsx
interface PlayerStatsProps {
  level: number;
  experience: number;
  nextLevelExp: number;
}

const PlayerStats: React.FC<PlayerStatsProps> = ({
  level,
  experience,
  nextLevelExp
}) => {
  const progressPercentage = (experience / nextLevelExp) * 100;

  return (
    <div className="player-stats">
      <h3>Level {level}</h3>
      <div className="progress-bar">
        <div 
          className="progress-fill" 
          style={{ width: `${progressPercentage}%` }}
        />
      </div>
      <p>{experience}/{nextLevelExp} XP</p>
    </div>
  );
};

// components/game/PlayerInventory.tsx - Separate component
// components/game/PlayerActions.tsx - Separate component
```

---

# Backend Standards

This document covers backend development standards for PHP projects.

## 🔧 Backend Standards (PHP)

### Required Dependencies (composer.json)

For lightweight projects, use a minimal dependency approach with a custom router:

```json
{
  "require": {
    "php": "^8.1",
    "vlucas/phpdotenv": "^5.5",
    "firebase/php-jwt": "^6.0"
  },
  "require-dev": {
    "phpunit/phpunit": "^10.0",
    "squizlabs/php_codesniffer": "^3.7"
  },
  "scripts": {
    "start": "php -S localhost:8000 -t public/",
    "test": "phpunit",
    "cs-check": "phpcs --standard=PSR12 src/ tests/",
    "cs-fix": "phpcbf --standard=PSR12 src/ tests/"
  },
  "autoload": {
    "psr-4": {
      "App\\": "src/"
    }
  }
}
```

### Architecture Pattern: Action-Based with ServiceFactory

Use an Action-based pattern with a centralized ServiceFactory for dependency management:

```
src/
├── Actions/           # Business logic organized by domain
│   ├── Auth/          # Authentication actions
│   ├── Character/     # Character CRUD actions
│   ├── Campaign/      # Campaign actions
│   └── Data/          # Data retrieval actions
├── Constants/         # Game rules and HTTP status codes
├── Core/              # Framework-like infrastructure
│   ├── Request.php    # Request wrapper
│   ├── Response.php   # Response builder
│   ├── Router.php     # Simple switch-based router
│   └── ServiceFactory.php  # Dependency container
├── External/          # Database repositories
├── Helpers/           # Response helpers
├── Middleware/        # Authentication middleware
├── Models/            # Data models (optional: Eloquent)
└── Services/          # Business services (AuthService, etc.)
```


### Username/Password Authentication (MANDATORY)
For projects requiring authentication, implement username/password authentication using these standardized patterns:

#### Authentication Service (Required)
```php
<?php
// ✅ CORRECT: Standardized AuthService
declare(strict_types=1);

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

final class AuthService
{
    private string $jwtSecret;
    private string $jwtIssuer;
    private int $jwtExpiration = 86400; // 24 hours
    
    public function __construct()
    {
        $this->jwtSecret = $_ENV['JWT_SECRET'] ?? '';
        $this->jwtIssuer = $_ENV['JWT_ISSUER'] ?? 'localhost';
        
        if (empty($this->jwtSecret)) {
            throw new \Exception('JWT configuration missing. Set JWT_SECRET environment variable.');
        }
    }
    
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
    
    public function generateToken(int $userId, string $username, string $email): string
    {
        $issuedAt = time();
        $expiresAt = $issuedAt + $this->jwtExpiration;
        
        $payload = [
            'iss' => $this->jwtIssuer,
            'iat' => $issuedAt,
            'exp' => $expiresAt,
            'sub' => $userId,
            'username' => $username,
            'email' => $email
        ];
        
        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }
    
    public function validateToken(string $token): array
    {
        try {
            // Set leeway for clock skew (5 minutes)
            JWT::$leeway = 300;
            
            // Decode and validate token
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            
            // Convert to array
            return (array) $decoded;
            
        } catch (\Exception $e) {
            throw new \Exception('Token validation failed: ' . $e->getMessage());
        }
    }
}
```

#### Authentication Middleware (Required)
```php
<?php
// ✅ CORRECT: Standardized AuthMiddleware
declare(strict_types=1);

namespace App\Middleware;

use App\Services\AuthService;
use App\External\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly UserRepository $userRepository
    ) {}

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        // Get Authorization header
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->createUnauthorizedResponse('Authorization header missing or invalid');
        }

        $token = substr($authHeader, 7); // Remove "Bearer " prefix

        try {
            // Validate the JWT token
            $payload = $this->authService->validateToken($token);
            
            // Get user from database
            $userId = (int) $payload['sub'];
            $user = $this->userRepository->findById($userId);
            
            if (!$user || !$user->is_active) {
                return $this->createUnauthorizedResponse('User not found or inactive');
            }
            
            // Add user info to request
            $request = $request->withAttribute('user', $user);
            $request = $request->withAttribute('user_id', $user->id);

            return $handler->handle($request);

        } catch (\Exception $e) {
            error_log("Auth Middleware Error: " . $e->getMessage());
            return $this->createUnauthorizedResponse('Token validation failed');
        }
    }

    private function createUnauthorizedResponse(string $message = 'Unauthorized'): Response
    {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => $message,
            'error' => 'Authentication required'
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }
}
```

#### Authentication Actions (Required)
```php
<?php
// ✅ CORRECT: User registration action
declare(strict_types=1);

namespace App\Actions\Auth;

use App\External\UserRepository;
use App\Services\AuthService;
use App\Models\User;

final class RegisterUserAction
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly AuthService $authService
    ) {}

    public function execute(string $username, string $email, string $password): User
    {
        // Validate input
        if (empty($username) || empty($email) || empty($password)) {
            throw new \InvalidArgumentException('Username, email, and password are required');
        }
        
        if (strlen($password) < 8) {
            throw new \InvalidArgumentException('Password must be at least 8 characters');
        }
        
        // Check if user already exists
        if ($this->userRepository->findByEmail($email)) {
            throw new \InvalidArgumentException('Email already registered');
        }
        
        if ($this->userRepository->findByUsername($username)) {
            throw new \InvalidArgumentException('Username already taken');
        }

        // Create new user
        $user = new User();
        $user->username = $username;
        $user->email = $email;
        $user->password_hash = $this->authService->hashPassword($password);
        $user->is_active = true;
        $user->created_at = new \DateTime();
        $user->updated_at = new \DateTime();
        
        $user = $this->userRepository->create($user);
        
        // Add default role if role system exists
        if (method_exists($user, 'assignRole')) {
            $user->assignRole('user');
        }
        
        return $user;
    }
}

// ✅ CORRECT: User login action
final class LoginUserAction
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly AuthService $authService
    ) {}

    public function execute(string $usernameOrEmail, string $password): array
    {
        // Find user by username or email
        $user = $this->userRepository->findByUsername($usernameOrEmail)
            ?? $this->userRepository->findByEmail($usernameOrEmail);
        
        if (!$user) {
            throw new \InvalidArgumentException('Invalid credentials');
        }
        
        // Verify password
        if (!$this->authService->verifyPassword($password, $user->password_hash)) {
            throw new \InvalidArgumentException('Invalid credentials');
        }
        
        // Check if user is active
        if (!$user->is_active) {
            throw new \InvalidArgumentException('Account is inactive');
        }
        
        // Generate JWT token
        $token = $this->authService->generateToken($user->id, $user->username, $user->email);
        
        return [
            'token' => $token,
            'user' => $user
        ];
    }
}
```

#### Environment Variables (Required)
```env
# JWT Configuration (Required for authenticated projects)
JWT_SECRET=your-secret-key-min-32-characters-long
JWT_ISSUER=your-app-name
```

### Actions Pattern (MANDATORY)
```php
<?php
// ✅ CORRECT: Actions contain business logic
declare(strict_types=1);

namespace App\Actions;

use App\External\UserRepository;
use App\Models\User;

final class CreateUserAction
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}

    public function execute(string $name, string $email): User
    {
        // Validation
        if (empty($name) || empty($email)) {
            throw new \InvalidArgumentException('Name and email are required');
        }

        // Business logic
        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->created_at = new \DateTime();

        // Persistence
        return $this->userRepository->create($user);
    }
}
```

### Controller Standards (Thin Layer)
```php
<?php
// ✅ CORRECT: Controllers are thin HTTP handlers
declare(strict_types=1);

namespace App\Controllers;

use App\Actions\CreateUserAction;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class UserController
{
    public function __construct(
        private readonly CreateUserAction $createUserAction
    ) {}

    public function create(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = json_decode($request->getBody()->getContents(), true);
            
            $user = $this->createUserAction->execute($data['name'], $data['email']);
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $user->toArray()
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
}
```

### Model Standards (Eloquent)
```php
<?php
// ✅ CORRECT: Typed Eloquent models
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class User extends Model
{
    protected $table = 'users';
    
    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'first_name',
        'last_name',
        'is_active'
    ];

    protected $hidden = [
        'password_hash'  // Never expose password hash in API responses
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
```

### Repository Pattern (MANDATORY)
```php
<?php
// ✅ CORRECT: Repository for data access
declare(strict_types=1);

namespace App\External;

use App\Models\User;

final class UserRepository
{
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function create(User $user): User
    {
        $user->save();
        return $user;
    }

    public function update(User $user): User
    {
        $user->save();
        return $user;
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findByUsername(string $username): ?User
    {
        return User::where('username', $username)->first();
    }
}
```

### Service Standards
```php
<?php
// ✅ CORRECT: Services for complex business logic
declare(strict_types=1);

namespace App\Services;

use App\External\UserRepository;
use App\Models\User;

final class UserService
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}

    public function calculateUserLevel(User $user): int
    {
        // Complex business logic
        return min(floor($user->experience / 1000) + 1, 100);
    }

    public function promoteUser(User $user): User
    {
        $newLevel = $this->calculateUserLevel($user);
        $user->level = $newLevel;
        
        return $this->userRepository->update($user);
    }
}
```

## 📁 File Organization Standards

### Backend File Naming  
- **Classes**: PascalCase (`UserController.php`, `CreateUserAction.php`)
- **Interfaces**: PascalCase with Interface suffix (`UserRepositoryInterface.php`)
- **Traits**: PascalCase with Trait suffix (`ApiResponseTrait.php`)

## ❌ Backend Prohibitions
- ❌ Business logic in Controllers
- ❌ Direct database queries in Controllers
- ❌ Missing type declarations (`declare(strict_types=1)`)
- ❌ SQL injection vulnerabilities (use Eloquent/prepared statements)
- ❌ Missing error handling
- ❌ Storing passwords in plain text (always use bcrypt hashing)
- ❌ Weak password requirements (minimum 8 characters)
- ❌ Missing JWT secret validation
- ❌ Missing required dependencies (monolog, respect/validation, firebase/php-jwt)
- ❌ Incorrect PHP version format (use "^8.1", not ">=8.1")
- ❌ Missing composer scripts (test, cs-check, cs-fix)
- ❌ Environment variable fallbacks (fail fast on missing config)

---

# Code Review Guidelines

Review the entire codebase and evaluate it according to the following
standards. Provide findings with concrete examples and recommended
improvements. Classify issues as **Critical / Major / Minor /
Suggestion**.

## 1. Architecture & Project Structure

### 1.1 Overall

-   Frontend and backend must be cleanly separated.
-   No shared business logic between client and server.
-   Clear and predictable folder structure:
    -   `/frontend`
    -   `/backend`
    -   `/docs`
    -   `/config`
-   No circular dependencies or cross-layer leaks.
-   Environment variables handled securely and never committed.

## 2. Frontend Code Review (React + TypeScript + Tailwind)

### 2.1 Frontend Structure

-   Recommended structure:
    -   `components/`
    -   `features/` or `modules/`
    -   `pages/`
    -   `hooks/`
    -   `services/` or `api/`
    -   `state/`
    -   `utils/`
    -   `types/`
    -   `assets/`
    -   `config/`
-   No monolithic components (\>300 lines).
-   Files must have a clear single responsibility.

### 2.2 Separation of Concerns

-   Components = UI only.
-   Hooks = logic only.
-   Services/API modules = data access only.
-   No direct `fetch()` in components.
-   No business logic inside components.

### 2.3 TypeScript Strictness

-   Strict mode enabled:

        "strict": true

-   No:

    -   `any`
    -   implicit types
    -   unsafe type assertions

-   Props & hooks must have explicit types.

-   API responses must be typed and validated.

### 2.4 Tailwind Best Practices

-   Classes organized logically:
    -   layout → spacing → typography → color → state
-   Avoid unreadable Tailwind "class soup".
-   Avoid mixing inline Tailwind with raw CSS unless necessary.
-   Shared patterns extracted into components or utility functions.
-   Use Tailwind theme customization for:
    -   colors
    -   spacing
    -   font sizes
    -   breakpoints

### 2.5 React Best Practices

-   Functional components only.
-   Prefer composition \> prop drilling.
-   Avoid unnecessary re-renders:
    -   memoization where appropriate
    -   stable dependencies in hooks
-   Side effects properly managed with `useEffect`.
-   No async directly inside `useEffect` bodies.

### 2.6 UI/UX Quality

-   Consistent spacing scale.
-   Consistent design system for:
    -   buttons
    -   cards
    -   form controls
    -   alerts/toasts
-   Accessibility:
    -   semantic HTML
    -   ARIA labels
    -   keyboard support
    -   adequate contrast
-   Loading, error, and empty states implemented for all async flows.

## 3. Backend Code Review (PHP + MySQL)

### 3.1 PHP Architecture

-   Clear separation of layers:
    -   Controllers (HTTP layer)
    -   Services (business logic)
    -   Repositories/DB layer (MySQL access)
    -   Models/DTOs
    -   Utils/helpers
    -   Config
-   No SQL queries inside controllers.
-   Services must not contain:
    -   raw SQL
    -   HTML output
-   Controllers must:
    -   validate input
    -   handle request/response
    -   call service layer
    -   return standardized responses

### 3.2 MySQL & Database Access

-   Database logic must be isolated in:
    -   Repository classes
    -   Data models
-   Use prepared statements for all queries.
-   No dynamic SQL built from unvalidated input.
-   Check for:
    -   indexes on frequently queried columns
    -   safe limits (avoid SELECT \*)
    -   reasonable error handling
-   No SQL embedded in strings inside views/components.

### 3.3 Security Requirements

-   Validate & sanitize all user input.
-   Use password_hash / password_verify for authentication.
-   No sensitive data returned to frontend.
-   Protect against:
    -   SQL injection
    -   XSS (frontend + API output)
    -   CSRF (if session-based auth)
    -   Directory traversal
-   Error messages must not expose stack traces in production.

### 3.4 Config & Environment

-   All config values must come from environment variables or config
    files.
-   No hardcoded credentials.
-   Missing critical environment values must:
    -   fail fast or default to safe behaviour
    -   never fallback silently

## 4. API Layer (Frontend ↔ Backend Boundary)

### 4.1 API Contracts

-   API endpoints must:
    -   be stable
    -   be versioned if needed
    -   use predictable naming
-   Request/response schemas should be clearly defined.

### 4.2 Error Handling

-   Frontend must gracefully handle:
    -   network errors
    -   server errors
    -   validation errors
-   Backend must:
    -   return structured errors (`status`, `message`, `code`)
    -   avoid raw HTML error pages for API routes

## 5. Code Quality & Cleanliness

### 5.1 General

-   No dead code or unused imports.
-   No duplicated code.
-   Consistent naming conventions across frontend & backend.
-   No deeply nested logic; prefer early returns.
-   Use constants instead of magic numbers/strings.
-   Logically group functions and utilities.

## 6. Testing (Frontend + Backend)

### 6.1 Frontend (React/TS)

-   Unit tests for:
    -   hooks
    -   pure functions
    -   small components
-   Integration tests for:
    -   API calls
    -   major UI flows
-   No reliance on real backend during tests.

### 6.2 Backend (PHP)

-   Unit tests for:
    -   services
    -   helpers
    -   business logic
-   Integration tests for:
    -   repositories
    -   API endpoints
-   Use an isolated test database.
-   No tests should modify real data.

## 7. Performance Considerations

## Frontend

-   Avoid unnecessary re-renders.
-   Lazy load heavy pages.
-   Optimize list rendering with keys and memo.
-   Avoid large bundle size.

## Backend

-   Efficient DB queries.
-   Caching where appropriate.
-   Avoid loading large data into memory unnecessarily.

## 8. Deliverables From Reviewer

The review must include:

1.  **Executive Summary**
2.  **Critical Issues**
3.  **Major Issues**
4.  **Minor Issues**
5.  **Suggested Improvements**
6.  **Frontend Scores**
    -   Architecture (0--10)
    -   TypeScript Quality (0--10)
    -   UI/UX (0--10)
    -   Component Quality (0--10)
7.  **Backend Scores**
    -   Architecture (0--10)
    -   Security (0--10)
    -   Database Structure (0--10)
    -   Reliability (0--10)
8.  **Overall Recommendation**

---

# End of Merged Standards and Code Review Document