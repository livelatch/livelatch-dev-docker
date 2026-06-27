# AI Use Policy

**Applies to:** theme creators, art and card creators (LatchDeck), integration builders, and anyone with Livelatch SDK access.
**Status:** Alpha 1.0 · **Effective:** 27 June 2026 · **Owner:** Livelatch

---

## 1. Our stance on AI

Livelatch believes generative AI has a real place as a **tool that assists development** — it speeds up the boring parts, helps people who aren't full-time engineers ship real work, and lowers the barrier to building on our platform.

But a tool is all it is. **Generative AI does not equal human creativity and ingenuity.** The originality, taste, and intent that make a theme feel alive or a card feel collectible come from a person, not a prompt. This policy exists to keep that line honest: AI is welcome where it assists a human creator, it is not welcome where it replaces one, and either way the people using your work deserve to know.

This is an alpha policy. It will change as the platform, the law, and the technology change. When it does, we'll version it and tell you what moved.

---

## 2. The short version

| Asset type | AI-generated allowed? | Must disclose? |
|---|---|---|
| Code (themes, integrations, SDK work) | ✅ Yes | ✅ Yes — in the manifest |
| Static art — PNG, JPEG, SVG, etc. | ❌ No | — |
| Video | ❌ No | — |
| Music / audio | ❌ No | — |
| Copy / text content | ⚠️ Allowed with disclosure | ✅ Yes |

Themes carry an **AI category**: `No AI`, `AI Assisted`, or `AI Generated`.
- **AI Assisted** themes **may be sold.**
- **AI Generated** themes **may not be sold** (they can still be shared for free).

If you're ever unsure which bucket you're in, pick the more honest one and ask us.

---

## 3. Disclosure is the core rule

If AI touched the work you submit to Livelatch, **say so.** Non-disclosure — passing AI output off as fully human-made — is the one thing this policy treats as a hard violation, more serious than the AI use itself.

### What counts as "AI"

Disclosure covers any generative AI model or service used to produce part of your submission, including but not limited to:

- **Approved assistants** — [Anthropic Claude](https://www.anthropic.com/claude), [OpenAI ChatGPT / Codex](https://openai.com/chatgpt), [Google Gemini](https://gemini.google.com/), [GitHub Copilot](https://github.com/features/copilot).
- **Third-party tools that use those models under the hood** — for example an IDE like [Cursor](https://cursor.com/), a design tool, or a "no-code" builder whose autocomplete, chat, or generation feature is powered by one of the LLMs above. If the feature you used is "powered by GPT / Claude / Gemini," it counts.

When you disclose, name the tool(s) where you can. "Claude" or "GitHub Copilot in VS Code" is enough; you don't need to attach transcripts.

---

## 4. What is **not** allowed

The following may **never** be AI-generated in anything you publish on Livelatch, in any tier (free or paid):

- **Static art** — PNGs, JPEGs, SVGs, icons, avatars, backgrounds, textures, card faces, or any still image. Card art and theme imagery must be made by a human (illustrated, photographed, designed, or hand-built in code).
- **Video** — any AI-generated or AI-extended moving image.
- **Music and audio** — any AI-generated track, loop, sound effect, or voice.

This applies whether the asset is the headline of your submission or a small detail inside it. Using an AI image generator to "just make a quick background" is still not allowed.

> **Why the carve-out for art, video, and music but not code?** Code is a means to an end — it implements a human's design. Generated images, video, and music *are* the creative output itself, and that's exactly the human-made part Livelatch wants to protect. (See §1.)

---

## 5. What **is** allowed — generated code

You may use AI to help write **code**: themes, integration logic, SDK projects, build scripts, and so on. This includes everything from inline autocomplete to asking a model to draft a whole function.

**You must disclose it in the manifest of the thing you ship** (see §7), and you remain fully responsible for what that code does (see §9).

AI may also assist with **text/copy** (descriptions, docs, labels) — disclose it the same way.

---

## 6. Theme AI categories

Every theme submitted to Livelatch declares one of three categories. Be honest about which one fits; the test is **who authored the creative work**, not how many tools were involved.

### `No AI`
No generative AI was used anywhere in the theme — not for code, copy, or assets. Hand-authored throughout.

### `AI Assisted`  — *sellable*
A **human authored the theme**, with AI helping along the way. The creative direction, structure, and decisions are yours; AI sped you up. Typical examples:

- [GitHub Copilot](https://github.com/features/copilot) autocomplete in VS Code.
- [Cursor](https://cursor.com/) or a similar AI-enabled IDE for refactoring, debugging, or boilerplate.
- Asking Claude/ChatGPT/Gemini to explain an API, fix a bug, or scaffold a snippet you then shaped.

AI Assisted themes **may be sold** on Livelatch.

### `AI Generated`  — *not sellable*
AI produced the **bulk of the original creative work** and you used it largely as-is — for example, prompting a model to "build me a full neon theme" and shipping close to what it returned. The expression originates from the model, not from you.

AI Generated themes **may not be sold.** They are welcome as **free** themes, clearly badged so consumers know what they're getting.

> Rule of thumb: if you removed the AI's contribution and there'd be no theme left, it's **AI Generated**. If there'd still be your theme — just slower to have built — it's **AI Assisted**.

Remember: even an AI Generated theme still may not contain AI-generated **art, video, or music** (§4). The "generated" category refers to its **code and design**, which is the only thing AI is permitted to generate here.

---

## 7. How to disclose — the manifest

Themes already ship a `manifest.json` (see [`docs/themes/blade-theme-system.md`](../themes/blade-theme-system.md)). AI disclosure lives there in an `ai` block:

```json
{
  "name": "Neon Drift",
  "slug": "neon-drift",
  "tier": "free",
  "ai": {
    "category": "assisted",
    "tools": ["GitHub Copilot", "Claude"],
    "scope": ["code", "text"],
    "notes": "Copilot autocomplete for the WebGL setup; copy drafted with Claude and edited by hand."
  }
}
```

| Field | Values | Meaning |
|---|---|---|
| `category` | `none` · `assisted` · `generated` | The theme's AI category (§6). |
| `tools` | array of names | Which AI tools were used. Omit or `[]` when `category` is `none`. |
| `scope` | `code` · `text` | What AI touched. **Never** `art`, `video`, or `audio` — those aren't permitted. |
| `notes` | free text | Optional, one line on how AI was used. |

Integrations and SDK projects disclose the same way — in their package/manifest metadata or a top-level `AI.md`. If your delivery format has no manifest, include the same `ai` block in your README or submission notes.

A missing or false `ai` block is treated as a disclosure violation (§10).

---

## 8. Consumer signaling

Disclosure isn't just paperwork — it's so **consumers know what they're adopting.** Livelatch surfaces the AI category to people browsing and applying your work:

- Themes show an **AI badge** in the Theme Studio and Theme Manager (`No AI`, `AI Assisted`, or `AI Generated`), driven by the manifest `ai.category`.
- AI Generated work is clearly marked as free-only and labelled as AI Generated wherever it's offered.
- The same signal travels with the asset so that anyone downstream stays informed.

The goal is simple: **nobody should use something generated by AI without knowing it.**

---

## 9. You are accountable for AI output

Using AI does not transfer responsibility to the tool.

- **Correctness & safety** — you own what your code does, including anything an AI wrote. Insecure, broken, or malicious behaviour is on you regardless of how it was generated.
- **Rights & licensing** — you warrant you have the right to publish what you submit. AI output can reproduce third-party or licensed material; if it does, that's your problem to catch before you ship.
- **No passing-off** — don't label AI Generated work as human-made, and don't claim originality you don't have.
- **No other people's IP or likeness** — don't use AI to clone another creator's brand, art style as a knockoff, or a real person's likeness/voice.
- **Privacy** — don't paste Livelatch user data or anyone's personal information into AI tools.
- **Accessibility & quality standards still apply** — AI assistance is not an excuse for a theme that breaks the icon/link contract or fails our review.

---

## 10. Enforcement

We'd rather coach than punish, especially in alpha. But:

- **Non-disclosure** (hiding AI use, mislabelling the category, selling an AI Generated theme) can lead to the work being unlisted, the sale reversed, and — for repeat or deliberate cases — loss of creator/SDK access.
- **Banned-asset use** (AI-generated art, video, or music) means the submission is rejected or removed until the asset is replaced with a compliant one.
- We may ask, in good faith, how a piece was made. Honest answers keep you in good standing even if something needs fixing.

---

## 11. Livelatch's own AI pipelines (our disclosure)

We hold ourselves to this policy. In the spirit of §3, here's how Livelatch itself uses AI today:

- **Platform development (code):** Livelatch's codebase is built with AI coding assistants working under human direction and review — currently **Anthropic Claude** (via Claude Code), **OpenAI Codex**, and **BitsAI**. Every AI-assisted change is recorded in our internal changelog (`summary.md`) and reviewed by a human before it ships. In our own terms, Livelatch's platform code is **AI Assisted**, not AI Generated — the architecture, product decisions, and final review are human.
- **Documentation:** Some docs (including drafts of this one) are AI-assisted and human-edited.
- **What we do *not* use generative AI for:** our brand, logos, UI illustration, marketing imagery, audio, and video are **human-made**. We don't generate the art, music, or video in the product — the same rule we ask of you (§4).
- **Operational tooling** (analytics, email, infrastructure) is not generative-content tooling and is out of scope for this section.

If any of this changes, we'll update this section and date it.

---

## 12. Definitions

- **Generative AI** — a model or service that produces new content (code, text, images, audio, video) from a prompt or context.
- **AI Assisted** — a human is the author; AI helped. Sellable.
- **AI Generated** — AI is the substantial author of the creative work. Free-only.
- **Disclosure** — declaring, in the manifest or submission, that AI was used and which tools.
- **Asset** — any file or content you publish: code, images, audio, video, or text.

---

## 13. Questions, edge cases, and changes

This policy can't anticipate everything, and the honest answer to a genuine edge case is "ask us." Reach out before you publish if you're unsure — getting it right up front is always cheaper than a takedown.

When this policy changes, the version and effective date at the top will move, and material changes will be summarised for creators.

*Livelatch · AI Use Policy · Alpha 1.0 · 27 June 2026*
