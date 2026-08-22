# 🧠 The Rambler (legacy capture reference)

> Thread is the canonical product and API for the merged diary offering. It combines this project's raw thought capture with Thread's searchable diary and reviewable memory layer. This folder is retained as migration-only legacy reference code; it receives no new features and is scheduled for removal after the migration and deployment review on **2026-12-31**.

**The Rambler is where you put all your non-sensical text, written with no spell check and no deep thoughts.**

Dump your raw thoughts. AI extracts the useful kernels of knowledge in a nice, easy-to-read summary.

## 🧭 Philosophy Rules
- **User stays in control**: Nothing auto-acts on their behalf.
- **No artificial limits on thinking**: The tool reflects, it does not instruct.
- **Security**: Data is stored on a server in plain text. Security should not be assumed at this stage. Manual delete means actually gone.
- **Transparency**: Money gates convenience, not cognition.

## 🌱 Core Experience
- **✍️ Capture**: Unlimited raw rambling text and basic voice-to-text.
- **🧩 Gentle Extraction**: Summaries, topics, questions, and ideas flagged (not ranked).
- **🔒 Control**: Nothing is auto-saved to tasks or goals without explicit consent.

## 💬 Tone & UI Language
We avoid "optimization" and "coaching" language.
- **Bad**: “Let’s optimize your productivity!” / **Good**: “Here’s what stood out, in case it helps.”
- **Bad**: “You seem stressed.” / **Good**: “This section reads heavier than the rest. You can ignore this.”

## 🚫 What We Explicitly Do Not Do
- ❌ No streaks or productivity shaming.
- ❌ No “AI coach” or "Growth partner" personas.
- ❌ No forced task systems or daily notifications.
- ❌ No emotional manipulation copy.

---

## 🏗️ Getting Started

This migration-only folder is not published or run as a standalone app. Use the canonical Thread app for development and deployment. Shared dependencies are installed from the monorepo root with `npm ci` and `composer install`; database changes are applied through Thread's backend migration workflow.

> [!TIP]
> **Windows Users**: If using PowerShell 5.1, run commands individually. For a smoother experience, use the integrated terminal in VS Code.
- [MVP Plan](file:///h:/WebHatchery/apps/rambler/MVP.md)
- [Feature Roadmap](file:///h:/WebHatchery/apps/rambler/FEATURES.md)
- [Monetization & Privacy](file:///h:/WebHatchery/apps/rambler/MONETIZATION.md)
