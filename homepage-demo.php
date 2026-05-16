<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Livelatch Homepage Demo</title>
    <meta name="description" content="Static Livelatch landing page, LatchID auth, and onboarding prototype.">
    <style>
        :root {
            --ll-bg: #090b18;
            --ll-bg-soft: #11162a;
            --ll-panel: rgba(19, 24, 48, 0.68);
            --ll-panel-strong: rgba(25, 31, 61, 0.86);
            --ll-line: rgba(255, 255, 255, 0.14);
            --ll-line-strong: rgba(255, 255, 255, 0.22);
            --ll-text: #f8fbff;
            --ll-muted: #b8c3df;
            --ll-soft: #dce6ff;
            --ll-purple: #8c5cff;
            --ll-blue: #27c3ff;
            --ll-pink: #ff5ab8;
            --ll-mint: #3dffd0;
            --ll-amber: #ffd166;
            --ll-shadow: 0 24px 70px rgba(0, 0, 0, 0.42);
            --ll-radius: 28px;
            --ll-radius-sm: 18px;
            --ll-max: 1160px;
            color-scheme: dark;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            line-height: 1.5;
            color: var(--ll-text);
            background:
                radial-gradient(circle at 13% 12%, rgba(140, 92, 255, 0.28), transparent 28rem),
                radial-gradient(circle at 88% 8%, rgba(39, 195, 255, 0.22), transparent 30rem),
                radial-gradient(circle at 55% 75%, rgba(255, 90, 184, 0.15), transparent 26rem),
                linear-gradient(145deg, #080914 0%, #10142a 44%, #08111f 100%);
            overflow-x: hidden;
        }

        body.ll-modal-open {
            overflow: hidden;
        }

        a {
            color: inherit;
        }

        button,
        a.ll-button {
            font: inherit;
        }

        button {
            border: 0;
        }

        .ll-page {
            position: relative;
            min-height: 100vh;
            overflow: hidden;
        }

        .ll-page::before {
            position: fixed;
            inset: 0;
            z-index: -2;
            pointer-events: none;
            content: "";
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.035) 1px, transparent 1px);
            background-size: 72px 72px;
            mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.72), transparent 80%);
        }

        .ll-shell {
            width: min(var(--ll-max), calc(100% - 40px));
            margin: 0 auto;
        }

        .ll-nav {
            position: sticky;
            top: 0;
            z-index: 20;
            backdrop-filter: blur(22px);
            background: linear-gradient(to bottom, rgba(9, 11, 24, 0.88), rgba(9, 11, 24, 0.56));
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .ll-nav-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 78px;
            gap: 18px;
        }

        .ll-brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .ll-logo {
            position: relative;
            display: grid;
            width: 44px;
            height: 44px;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            background:
                linear-gradient(135deg, rgba(140, 92, 255, 0.98), rgba(39, 195, 255, 0.84)),
                #171b32;
            box-shadow: 0 14px 36px rgba(39, 195, 255, 0.22);
        }

        .ll-logo::before,
        .ll-logo::after {
            position: absolute;
            content: "";
            border-radius: 999px;
            background: white;
        }

        .ll-logo::before {
            width: 18px;
            height: 7px;
            transform: rotate(-35deg) translate(-3px, -1px);
        }

        .ll-logo::after {
            width: 7px;
            height: 18px;
            transform: rotate(-35deg) translate(4px, 3px);
            opacity: 0.72;
        }

        .ll-brand-name {
            display: block;
            font-size: 1.15rem;
            font-weight: 850;
        }

        .ll-brand-note {
            display: block;
            margin-top: -3px;
            color: var(--ll-muted);
            font-size: 0.78rem;
            font-weight: 650;
        }

        .ll-nav-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .ll-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0 18px;
            gap: 8px;
            border-radius: 999px;
            color: var(--ll-text);
            cursor: pointer;
            text-decoration: none;
            transition: transform 180ms ease, border-color 180ms ease, background 180ms ease, box-shadow 180ms ease;
        }

        .ll-button:hover,
        .ll-button:focus-visible {
            transform: translateY(-1px);
        }

        .ll-button:focus-visible,
        .ll-icon-button:focus-visible,
        .ll-provider:focus-visible,
        .ll-tab:focus-visible,
        .ll-close:focus-visible {
            outline: 3px solid rgba(61, 255, 208, 0.62);
            outline-offset: 3px;
        }

        .ll-button-primary {
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: linear-gradient(135deg, var(--ll-purple), var(--ll-blue));
            box-shadow: 0 18px 42px rgba(39, 195, 255, 0.22), inset 0 1px 0 rgba(255, 255, 255, 0.22);
            font-weight: 800;
        }

        .ll-button-primary:hover {
            box-shadow: 0 22px 58px rgba(140, 92, 255, 0.28), inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }

        .ll-button-ghost {
            border: 1px solid var(--ll-line);
            background: rgba(255, 255, 255, 0.06);
            color: var(--ll-soft);
            font-weight: 750;
        }

        .ll-button-ghost:hover {
            border-color: var(--ll-line-strong);
            background: rgba(255, 255, 255, 0.1);
        }

        .ll-hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(340px, 480px);
            align-items: center;
            gap: clamp(34px, 6vw, 76px);
            padding: clamp(58px, 8vw, 108px) 0 46px;
        }

        .ll-alpha-pill {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            width: fit-content;
            margin-bottom: 22px;
            padding: 9px 13px;
            border: 1px solid rgba(61, 255, 208, 0.32);
            border-radius: 999px;
            color: #eafffa;
            background: rgba(61, 255, 208, 0.09);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.12);
            font-size: 0.88rem;
            font-weight: 760;
        }

        .ll-alpha-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: var(--ll-mint);
            box-shadow: 0 0 18px rgba(61, 255, 208, 0.9);
        }

        .ll-hero h1 {
            max-width: 780px;
            margin: 0;
            font-size: clamp(3rem, 8vw, 6.8rem);
            line-height: 0.93;
            letter-spacing: 0;
        }

        .ll-gradient-text {
            background: linear-gradient(90deg, #fff 0%, #c7d8ff 32%, #72efff 66%, #ffc0e5 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .ll-hero-copy {
            max-width: 670px;
            margin: 24px 0 0;
            color: var(--ll-muted);
            font-size: clamp(1.08rem, 2vw, 1.26rem);
        }

        .ll-hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 30px;
        }

        .ll-proof {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 32px;
            color: var(--ll-muted);
            font-size: 0.92rem;
        }

        .ll-proof span {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .ll-proof span::before {
            width: 7px;
            height: 7px;
            content: "";
            border-radius: 999px;
            background: var(--ll-amber);
        }

        .ll-mock-stage {
            position: relative;
            min-height: 640px;
        }

        .ll-mock-stage::before {
            position: absolute;
            inset: 46px 12px 80px;
            content: "";
            border-radius: 40px;
            background: linear-gradient(135deg, rgba(140, 92, 255, 0.25), rgba(39, 195, 255, 0.18), rgba(255, 90, 184, 0.15));
            filter: blur(34px);
        }

        .ll-profile-card,
        .ll-deck-card,
        .ll-moment-chip {
            position: absolute;
            border: 1px solid var(--ll-line);
            border-radius: var(--ll-radius);
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.14), rgba(255, 255, 255, 0.055));
            box-shadow: var(--ll-shadow);
            backdrop-filter: blur(24px);
        }

        .ll-profile-card {
            top: 22px;
            right: 16px;
            width: min(100%, 390px);
            padding: 20px;
        }

        .ll-profile-hero {
            min-height: 156px;
            border-radius: 22px;
            background:
                radial-gradient(circle at 22% 22%, rgba(255, 255, 255, 0.72), transparent 6rem),
                linear-gradient(135deg, rgba(255, 90, 184, 0.86), rgba(140, 92, 255, 0.88) 45%, rgba(39, 195, 255, 0.82));
            overflow: hidden;
        }

        .ll-profile-head {
            display: flex;
            align-items: flex-end;
            gap: 14px;
            margin-top: -34px;
            padding: 0 10px;
        }

        .ll-avatar {
            display: grid;
            width: 80px;
            height: 80px;
            place-items: center;
            border: 4px solid rgba(11, 13, 28, 0.94);
            border-radius: 24px;
            background: linear-gradient(135deg, #ffe5f3, #9ceeff);
            color: #10142a;
            font-size: 1.5rem;
            font-weight: 900;
        }

        .ll-profile-title {
            padding-bottom: 6px;
        }

        .ll-profile-title strong,
        .ll-deck-card strong {
            display: block;
            font-size: 1.08rem;
        }

        .ll-profile-title span,
        .ll-deck-card span {
            color: var(--ll-muted);
            font-size: 0.9rem;
        }

        .ll-link-list {
            display: grid;
            gap: 10px;
            margin-top: 18px;
        }

        .ll-link-pill {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 50px;
            padding: 0 14px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 17px;
            background: rgba(255, 255, 255, 0.075);
            color: #eef4ff;
            font-weight: 760;
        }

        .ll-link-pill span:last-child {
            color: var(--ll-mint);
        }

        .ll-deck-card {
            left: 18px;
            bottom: 122px;
            width: 310px;
            padding: 18px;
        }

        .ll-deck-strip {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 14px;
        }

        .ll-mini-card {
            min-height: 104px;
            padding: 10px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.08);
        }

        .ll-mini-card i {
            display: block;
            width: 100%;
            height: 46px;
            margin-bottom: 10px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(39, 195, 255, 0.74), rgba(255, 209, 102, 0.78));
        }

        .ll-mini-card:nth-child(2) i {
            background: linear-gradient(135deg, rgba(255, 90, 184, 0.8), rgba(140, 92, 255, 0.8));
        }

        .ll-mini-card:nth-child(3) i {
            background: linear-gradient(135deg, rgba(61, 255, 208, 0.72), rgba(39, 195, 255, 0.8));
        }

        .ll-mini-card b {
            display: block;
            font-size: 0.75rem;
        }

        .ll-mini-card small {
            color: var(--ll-muted);
            font-size: 0.7rem;
        }

        .ll-moment-chip {
            right: 0;
            bottom: 42px;
            display: flex;
            align-items: center;
            width: min(92%, 330px);
            gap: 12px;
            padding: 14px;
            border-radius: 22px;
        }

        .ll-spark {
            display: grid;
            flex: 0 0 auto;
            width: 48px;
            height: 48px;
            place-items: center;
            border-radius: 17px;
            background: rgba(255, 209, 102, 0.16);
            color: var(--ll-amber);
            font-size: 1.4rem;
        }

        .ll-moment-chip strong {
            display: block;
        }

        .ll-moment-chip span {
            color: var(--ll-muted);
            font-size: 0.88rem;
        }

        .ll-section {
            padding: 62px 0;
        }

        .ll-section-heading {
            max-width: 760px;
            margin-bottom: 30px;
        }

        .ll-section-heading p {
            margin: 10px 0 0;
            color: var(--ll-muted);
            font-size: 1.06rem;
        }

        .ll-section h2 {
            margin: 0;
            font-size: clamp(2rem, 4vw, 3.4rem);
            line-height: 1.05;
            letter-spacing: 0;
        }

        .ll-feature-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }

        .ll-feature {
            min-height: 300px;
            padding: 24px;
            border: 1px solid var(--ll-line);
            border-radius: var(--ll-radius);
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.12), rgba(255, 255, 255, 0.045));
            box-shadow: 0 20px 54px rgba(0, 0, 0, 0.2);
        }

        .ll-feature-icon {
            display: grid;
            width: 54px;
            height: 54px;
            place-items: center;
            margin-bottom: 22px;
            border-radius: 18px;
            background: rgba(39, 195, 255, 0.12);
            color: var(--ll-blue);
            font-size: 1.55rem;
        }

        .ll-feature:nth-child(2) .ll-feature-icon {
            background: rgba(255, 90, 184, 0.12);
            color: #ff9bd2;
        }

        .ll-feature:nth-child(3) .ll-feature-icon {
            background: rgba(61, 255, 208, 0.11);
            color: var(--ll-mint);
        }

        .ll-feature h3 {
            margin: 0 0 10px;
            font-size: 1.35rem;
        }

        .ll-feature p {
            margin: 0;
            color: var(--ll-muted);
        }

        .ll-feature-preview {
            display: grid;
            gap: 9px;
            margin-top: 24px;
        }

        .ll-preview-line {
            height: 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.1);
        }

        .ll-preview-line:nth-child(1) {
            width: 84%;
            background: linear-gradient(90deg, rgba(39, 195, 255, 0.74), rgba(61, 255, 208, 0.5));
        }

        .ll-preview-line:nth-child(2) {
            width: 62%;
        }

        .ll-preview-line:nth-child(3) {
            width: 74%;
        }

        .ll-identity-band {
            display: grid;
            grid-template-columns: 0.9fr 1.1fr;
            gap: 28px;
            align-items: stretch;
            padding: 28px;
            border: 1px solid var(--ll-line);
            border-radius: 34px;
            background:
                linear-gradient(135deg, rgba(140, 92, 255, 0.2), rgba(39, 195, 255, 0.1)),
                rgba(255, 255, 255, 0.055);
            box-shadow: var(--ll-shadow);
        }

        .ll-id-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 320px;
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 26px;
            background: rgba(5, 8, 20, 0.46);
        }

        .ll-id-card b {
            font-size: 1.2rem;
        }

        .ll-id-lock {
            width: 86px;
            height: 86px;
            border-radius: 28px;
            background:
                radial-gradient(circle at 35% 28%, rgba(255, 255, 255, 0.78), transparent 1.2rem),
                linear-gradient(135deg, var(--ll-purple), var(--ll-pink));
        }

        .ll-id-lines {
            display: grid;
            gap: 10px;
        }

        .ll-id-lines span {
            height: 13px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.13);
        }

        .ll-id-lines span:nth-child(1) {
            width: 76%;
        }

        .ll-id-lines span:nth-child(2) {
            width: 54%;
        }

        .ll-identity-copy {
            padding: 10px 8px;
        }

        .ll-identity-copy p {
            color: var(--ll-muted);
            font-size: 1.05rem;
        }

        .ll-footer {
            padding: 34px 0 48px;
            color: rgba(220, 230, 255, 0.72);
            font-size: 0.88rem;
        }

        .ll-modal {
            position: fixed;
            inset: 0;
            z-index: 100;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 22px;
            background: rgba(4, 6, 16, 0.68);
            backdrop-filter: blur(14px);
        }

        .ll-modal[aria-hidden="false"] {
            display: flex;
        }

        .ll-modal-panel {
            position: relative;
            width: min(100%, 540px);
            max-height: min(760px, calc(100vh - 44px));
            overflow: auto;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 30px;
            background:
                radial-gradient(circle at 20% 0%, rgba(140, 92, 255, 0.28), transparent 18rem),
                linear-gradient(145deg, rgba(21, 26, 52, 0.96), rgba(10, 13, 29, 0.96));
            box-shadow: 0 38px 110px rgba(0, 0, 0, 0.62);
        }

        .ll-modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            padding: 24px 24px 14px;
        }

        .ll-modal-kicker {
            margin: 0 0 4px;
            color: var(--ll-mint);
            font-size: 0.78rem;
            font-weight: 850;
            text-transform: uppercase;
        }

        .ll-modal-title {
            margin: 0;
            font-size: 1.8rem;
            line-height: 1.1;
        }

        .ll-close {
            display: grid;
            flex: 0 0 auto;
            width: 42px;
            height: 42px;
            place-items: center;
            border: 1px solid var(--ll-line);
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.07);
            color: white;
            cursor: pointer;
            font-size: 1.45rem;
            line-height: 1;
        }

        .ll-tabs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin: 0 24px 18px;
            padding: 6px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.055);
        }

        .ll-tab {
            min-height: 42px;
            border-radius: 13px;
            background: transparent;
            color: var(--ll-muted);
            cursor: pointer;
            font-weight: 800;
        }

        .ll-tab[aria-selected="true"] {
            color: white;
            background: linear-gradient(135deg, rgba(140, 92, 255, 0.78), rgba(39, 195, 255, 0.62));
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        .ll-modal-body {
            padding: 0 24px 24px;
        }

        .ll-panel-copy {
            margin: 0 0 18px;
            color: var(--ll-muted);
        }

        .ll-step {
            display: none;
        }

        .ll-step.ll-active {
            display: block;
        }

        .ll-step-card {
            padding: 20px;
            border: 1px solid var(--ll-line);
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.065);
        }

        .ll-step-card h3 {
            margin: 0 0 8px;
            font-size: 1.35rem;
        }

        .ll-step-card p {
            margin: 0;
            color: var(--ll-muted);
        }

        .ll-provider-list {
            display: grid;
            gap: 10px;
            margin-top: 16px;
        }

        .ll-provider {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            min-height: 54px;
            padding: 0 14px;
            border: 1px solid rgba(255, 255, 255, 0.13);
            border-radius: 17px;
            background: rgba(255, 255, 255, 0.075);
            color: white;
            cursor: pointer;
            font-weight: 820;
            transition: transform 160ms ease, background 160ms ease, border-color 160ms ease;
        }

        .ll-provider:hover {
            transform: translateY(-1px);
            border-color: rgba(255, 255, 255, 0.24);
            background: rgba(255, 255, 255, 0.11);
        }

        .ll-provider span {
            display: inline-flex;
            align-items: center;
            gap: 12px;
        }

        .ll-provider-icon {
            display: grid;
            width: 32px;
            height: 32px;
            place-items: center;
            border-radius: 11px;
            background: rgba(255, 255, 255, 0.12);
            font-size: 0.92rem;
        }

        .ll-provider small {
            color: var(--ll-mint);
            font-size: 1rem;
        }

        .ll-helper {
            margin: 14px 0 0;
            color: var(--ll-muted);
            font-size: 0.92rem;
        }

        .ll-modal-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
        }

        .ll-demo-message {
            display: none;
            margin-top: 16px;
            padding: 14px;
            border: 1px solid rgba(61, 255, 208, 0.22);
            border-radius: 18px;
            background: rgba(61, 255, 208, 0.08);
            color: #e8fffb;
        }

        .ll-demo-message.ll-visible {
            display: block;
        }

        .ll-loading {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .ll-loading::before {
            width: 14px;
            height: 14px;
            content: "";
            border: 2px solid rgba(255, 255, 255, 0.28);
            border-top-color: white;
            border-radius: 999px;
            animation: ll-spin 800ms linear infinite;
        }

        @keyframes ll-spin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 920px) {
            .ll-hero,
            .ll-identity-band {
                grid-template-columns: 1fr;
            }

            .ll-mock-stage {
                min-height: 600px;
            }

            .ll-feature-grid {
                grid-template-columns: 1fr;
            }

            .ll-feature {
                min-height: auto;
            }
        }

        @media (max-width: 640px) {
            .ll-shell {
                width: min(100% - 28px, var(--ll-max));
            }

            .ll-nav-inner {
                min-height: 68px;
            }

            .ll-brand-note {
                display: none;
            }

            .ll-nav-actions {
                gap: 7px;
            }

            .ll-nav-actions .ll-button {
                min-height: 40px;
                padding: 0 12px;
                font-size: 0.88rem;
            }

            .ll-hero {
                padding-top: 42px;
            }

            .ll-hero-actions .ll-button {
                width: 100%;
            }

            .ll-mock-stage {
                min-height: 700px;
            }

            .ll-profile-card,
            .ll-deck-card,
            .ll-moment-chip {
                position: relative;
                inset: auto;
                width: 100%;
                margin-bottom: 16px;
            }

            .ll-mock-stage::before {
                inset: 30px 0;
            }

            .ll-deck-strip {
                grid-template-columns: 1fr;
            }

            .ll-identity-band {
                padding: 18px;
                border-radius: 26px;
            }

            .ll-modal {
                padding: 12px;
            }

            .ll-modal-panel {
                border-radius: 24px;
            }

            .ll-modal-header,
            .ll-modal-body {
                padding-left: 18px;
                padding-right: 18px;
            }

            .ll-tabs {
                margin-left: 18px;
                margin-right: 18px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                animation-duration: 1ms !important;
                transition-duration: 1ms !important;
            }
        }
    </style>
</head>
<body>
<div class="ll-page" id="llPage">
    <header class="ll-nav">
        <div class="ll-shell ll-nav-inner">
            <a class="ll-brand" href="#top" aria-label="Livelatch home">
                <span class="ll-logo" aria-hidden="true"></span>
                <span>
                    <span class="ll-brand-name">Livelatch</span>
                    <span class="ll-brand-note">Online Community as a Service</span>
                </span>
            </a>
            <nav class="ll-nav-actions" aria-label="Account actions">
                <button class="ll-button ll-button-ghost" type="button" data-ll-open-modal="login">Log in</button>
                <button class="ll-button ll-button-primary" type="button" data-ll-open-modal="signup">Create LatchID</button>
            </nav>
        </div>
    </header>

    <main id="top">
        <section class="ll-shell ll-hero" aria-labelledby="llHeroTitle">
            <div>
                <div class="ll-alpha-pill"><span class="ll-alpha-dot" aria-hidden="true"></span>Livelatch Alpha Experience</div>
                <h1 id="llHeroTitle">The creator profile that feels <span class="ll-gradient-text">alive.</span></h1>
                <p class="ll-hero-copy">
                    Livelatch is a link-in-bio and community identity platform for creators who value their viewers.
                    Build a Creator profile, collect Community moments, and give your people a LatchID they can carry across every drop, stream, and launch.
                </p>
                <div class="ll-hero-actions">
                    <button class="ll-button ll-button-primary" type="button" data-ll-open-modal="signup">Create LatchID</button>
                    <button class="ll-button ll-button-ghost" type="button" data-ll-open-modal="login">Log in</button>
                </div>
                <div class="ll-proof" aria-label="Livelatch platform highlights">
                    <span>Creator profile</span>
                    <span>LatchDeck drops</span>
                    <span>Community moments</span>
                </div>
            </div>

            <div class="ll-mock-stage" aria-label="Livelatch creator profile preview">
                <article class="ll-profile-card">
                    <div class="ll-profile-hero" aria-hidden="true"></div>
                    <div class="ll-profile-head">
                        <div class="ll-avatar" aria-hidden="true">LL</div>
                        <div class="ll-profile-title">
                            <strong>Nova Nights</strong>
                            <span>@novanights - live every Friday</span>
                        </div>
                    </div>
                    <div class="ll-link-list">
                        <div class="ll-link-pill"><span>Latest stream hub</span><span>Go</span></div>
                        <div class="ll-link-pill"><span>Members-only playlist</span><span>Go</span></div>
                        <div class="ll-link-pill"><span>Drop calendar</span><span>Go</span></div>
                    </div>
                </article>

                <article class="ll-deck-card">
                    <strong>LatchDeck</strong>
                    <span>Carousel cards for what is happening now.</span>
                    <div class="ll-deck-strip" aria-hidden="true">
                        <div class="ll-mini-card"><i></i><b>Drop</b><small>Today</small></div>
                        <div class="ll-mini-card"><i></i><b>Clip</b><small>Fan pick</small></div>
                        <div class="ll-mini-card"><i></i><b>Meet</b><small>RSVP</small></div>
                    </div>
                </article>

                <aside class="ll-moment-chip">
                    <span class="ll-spark" aria-hidden="true">*</span>
                    <span>
                        <strong>Community moment saved</strong>
                        <span>Viewer shoutout added to the profile timeline.</span>
                    </span>
                </aside>
            </div>
        </section>

        <section class="ll-shell ll-section" aria-labelledby="llFeaturesTitle">
            <div class="ll-section-heading">
                <h2 id="llFeaturesTitle">Built for communities that move fast.</h2>
                <p>Prototype copy and layout for testing the Livelatch landing experience before production OAuth and onboarding are connected.</p>
            </div>
            <div class="ll-feature-grid">
                <article class="ll-feature">
                    <div class="ll-feature-icon" aria-hidden="true">+</div>
                    <h3>Your links, but alive</h3>
                    <p>Turn a static link list into a living Creator profile with timely cards, featured links, and updates your viewers can actually follow.</p>
                    <div class="ll-feature-preview" aria-hidden="true">
                        <span class="ll-preview-line"></span>
                        <span class="ll-preview-line"></span>
                        <span class="ll-preview-line"></span>
                    </div>
                </article>
                <article class="ll-feature">
                    <div class="ll-feature-icon" aria-hidden="true">#</div>
                    <h3>Collect community moments</h3>
                    <p>Highlight stream wins, launch milestones, fan art, questions, clips, and the small signals that make viewers feel seen.</p>
                    <div class="ll-feature-preview" aria-hidden="true">
                        <span class="ll-preview-line"></span>
                        <span class="ll-preview-line"></span>
                        <span class="ll-preview-line"></span>
                    </div>
                </article>
                <article class="ll-feature">
                    <div class="ll-feature-icon" aria-hidden="true">ID</div>
                    <h3>A home for your creator identity</h3>
                    <p>LatchID and LatchDeck give creators a premium home base for community identity, discovery, and future member experiences.</p>
                    <div class="ll-feature-preview" aria-hidden="true">
                        <span class="ll-preview-line"></span>
                        <span class="ll-preview-line"></span>
                        <span class="ll-preview-line"></span>
                    </div>
                </article>
            </div>
        </section>

        <section class="ll-shell ll-section" aria-labelledby="llIdentityTitle">
            <div class="ll-identity-band">
                <div class="ll-id-card" aria-label="LatchID preview card">
                    <div class="ll-id-lock" aria-hidden="true"></div>
                    <div>
                        <b>LatchID</b>
                        <p class="ll-helper">One social-login identity for the future Livelatch creator and viewer experience.</p>
                    </div>
                    <div class="ll-id-lines" aria-hidden="true">
                        <span></span>
                        <span></span>
                    </div>
                </div>
                <div class="ll-identity-copy">
                    <div class="ll-alpha-pill"><span class="ll-alpha-dot" aria-hidden="true"></span>No password signup</div>
                    <h2 id="llIdentityTitle">OAuth-first onboarding for a cleaner creator start.</h2>
                    <p>
                        This static demo keeps account actions inside a modal so the team can test wording, provider choice,
                        and the dashboard handoff before connecting Supabase OAuth sessions.
                    </p>
                    <button class="ll-button ll-button-primary" type="button" data-ll-open-modal="signup">Try the LatchID flow</button>
                </div>
            </div>
        </section>
    </main>

    <footer class="ll-shell ll-footer">
        Static Livelatch prototype. Demo OAuth buttons do not authenticate. Dashboard handoff target:
        <a href="https://dev.livelatch.com/dashboard">dev.livelatch.com/dashboard</a>.
    </footer>
</div>

<div class="ll-modal" id="llAuthModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="llModalTitle">
    <div class="ll-modal-panel" role="document">
        <header class="ll-modal-header">
            <div>
                <p class="ll-modal-kicker">LatchID access</p>
                <h2 class="ll-modal-title" id="llModalTitle">Create your Livelatch identity</h2>
            </div>
            <button class="ll-close" type="button" data-ll-close-modal aria-label="Close modal">&times;</button>
        </header>

        <div class="ll-tabs" role="tablist" aria-label="Authentication mode">
            <button class="ll-tab" type="button" role="tab" id="llSignupTab" aria-controls="llSignupPanel" aria-selected="true" data-ll-tab="signup">Create LatchID</button>
            <button class="ll-tab" type="button" role="tab" id="llLoginTab" aria-controls="llLoginPanel" aria-selected="false" data-ll-tab="login">Log in</button>
        </div>

        <div class="ll-modal-body">
            <section id="llSignupPanel" role="tabpanel" aria-labelledby="llSignupTab" data-ll-panel="signup">
                <div class="ll-step ll-active" data-ll-step="welcome">
                    <div class="ll-step-card">
                        <h3>Welcome to LatchID</h3>
                        <p>
                            LatchID is your Livelatch identity. It will eventually connect your creator tools,
                            viewer community moments, and dashboard access through trusted social login.
                        </p>
                    </div>
                    <div class="ll-modal-actions">
                        <button class="ll-button ll-button-primary" type="button" data-ll-next-step>Choose a social account</button>
                    </div>
                </div>

                <div class="ll-step" data-ll-step="providers">
                    <p class="ll-panel-copy">Which social account do you want to use for your LatchID?</p>
                    <div class="ll-provider-list" data-ll-provider-list>
                        <button class="ll-provider" type="button" data-ll-provider="Google"><span><span class="ll-provider-icon">G</span>Continue with Google</span><small>OAuth</small></button>
                        <button class="ll-provider" type="button" data-ll-provider="Discord"><span><span class="ll-provider-icon">D</span>Continue with Discord</span><small>OAuth</small></button>
                        <button class="ll-provider" type="button" data-ll-provider="TikTok"><span><span class="ll-provider-icon">T</span>Continue with TikTok</span><small>OAuth</small></button>
                        <button class="ll-provider" type="button" data-ll-provider="Twitch"><span><span class="ll-provider-icon">Tw</span>Continue with Twitch</span><small>OAuth</small></button>
                    </div>
                    <p class="ll-helper">No passwords. Your LatchID is created through a trusted social login.</p>
                    <div class="ll-demo-message" data-ll-demo-message></div>
                </div>
            </section>

            <section id="llLoginPanel" role="tabpanel" aria-labelledby="llLoginTab" data-ll-panel="login" hidden>
                <p class="ll-panel-copy">Welcome back. Choose the social account linked to your LatchID.</p>
                <div class="ll-provider-list" data-ll-provider-list>
                    <button class="ll-provider" type="button" data-ll-provider="Google"><span><span class="ll-provider-icon">G</span>Continue with Google</span><small>OAuth</small></button>
                    <button class="ll-provider" type="button" data-ll-provider="Discord"><span><span class="ll-provider-icon">D</span>Continue with Discord</span><small>OAuth</small></button>
                    <button class="ll-provider" type="button" data-ll-provider="TikTok"><span><span class="ll-provider-icon">T</span>Continue with TikTok</span><small>OAuth</small></button>
                    <button class="ll-provider" type="button" data-ll-provider="Twitch"><span><span class="ll-provider-icon">Tw</span>Continue with Twitch</span><small>OAuth</small></button>
                </div>
                <p class="ll-helper">After authentication, you will be taken to your dashboard.</p>
                <div class="ll-demo-message" data-ll-demo-message></div>
            </section>
        </div>
    </div>
</div>

<script>
    (function () {
        'use strict';

        var modal = document.getElementById('llAuthModal');
        var modalTitle = document.getElementById('llModalTitle');
        var tabs = Array.prototype.slice.call(document.querySelectorAll('[data-ll-tab]'));
        var panels = Array.prototype.slice.call(document.querySelectorAll('[data-ll-panel]'));
        var openButtons = Array.prototype.slice.call(document.querySelectorAll('[data-ll-open-modal]'));
        var closeButtons = Array.prototype.slice.call(document.querySelectorAll('[data-ll-close-modal]'));
        var signupSteps = Array.prototype.slice.call(document.querySelectorAll('#llSignupPanel [data-ll-step]'));
        var nextStepButton = document.querySelector('[data-ll-next-step]');
        var lastFocused = null;
        var loadingTimer = null;

        function clearDemoMessages() {
            Array.prototype.slice.call(document.querySelectorAll('[data-ll-demo-message]')).forEach(function (message) {
                message.classList.remove('ll-visible');
                message.innerHTML = '';
            });
        }

        function resetSignupStep() {
            signupSteps.forEach(function (step) {
                step.classList.toggle('ll-active', step.getAttribute('data-ll-step') === 'welcome');
            });
        }

        function setMode(mode) {
            tabs.forEach(function (tab) {
                var isSelected = tab.getAttribute('data-ll-tab') === mode;
                tab.setAttribute('aria-selected', String(isSelected));
            });

            panels.forEach(function (panel) {
                var isActive = panel.getAttribute('data-ll-panel') === mode;
                panel.hidden = !isActive;
            });

            modalTitle.textContent = mode === 'login' ? 'Log in to Livelatch' : 'Create your Livelatch identity';
            clearDemoMessages();

            if (mode === 'signup') {
                resetSignupStep();
            }
        }

        function openModal(mode) {
            lastFocused = document.activeElement;
            setMode(mode || 'signup');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('ll-modal-open');

            window.setTimeout(function () {
                var selectedTab = modal.querySelector('[aria-selected="true"]');
                if (selectedTab) {
                    selectedTab.focus();
                }
            }, 20);
        }

        function closeModal() {
            window.clearTimeout(loadingTimer);
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('ll-modal-open');
            clearDemoMessages();

            if (lastFocused && typeof lastFocused.focus === 'function') {
                lastFocused.focus();
            }
        }

        function showProvidersStep() {
            signupSteps.forEach(function (step) {
                step.classList.toggle('ll-active', step.getAttribute('data-ll-step') === 'providers');
            });

            var firstProvider = document.querySelector('#llSignupPanel [data-ll-provider]');
            if (firstProvider) {
                firstProvider.focus();
            }
        }

        function handleProviderClick(button) {
            var provider = button.getAttribute('data-ll-provider');
            var panel = button.closest('[data-ll-panel]');
            var message = panel ? panel.querySelector('[data-ll-demo-message]') : null;

            if (!message) {
                return;
            }

            window.clearTimeout(loadingTimer);
            message.classList.add('ll-visible');
            message.innerHTML = '<span class="ll-loading">Connecting to ' + provider + '</span>';

            loadingTimer = window.setTimeout(function () {
                message.innerHTML =
                    '<strong>Demo only:</strong> this would redirect to the selected OAuth provider, then return you to /dashboard.' +
                    '<div class="ll-modal-actions">' +
                    '<a class="ll-button ll-button-primary" href="https://dev.livelatch.com/dashboard">Continue to dashboard</a>' +
                    '</div>';
            }, 700);
        }

        function trapFocus(event) {
            if (modal.getAttribute('aria-hidden') === 'true' || event.key !== 'Tab') {
                return;
            }

            var focusable = Array.prototype.slice.call(modal.querySelectorAll(
                'a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])'
            )).filter(function (element) {
                return element.offsetParent !== null;
            });

            if (!focusable.length) {
                return;
            }

            var first = focusable[0];
            var last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }

        openButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                openModal(button.getAttribute('data-ll-open-modal'));
            });
        });

        closeButtons.forEach(function (button) {
            button.addEventListener('click', closeModal);
        });

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                setMode(tab.getAttribute('data-ll-tab'));
            });
        });

        if (nextStepButton) {
            nextStepButton.addEventListener('click', showProvidersStep);
        }

        Array.prototype.slice.call(document.querySelectorAll('[data-ll-provider]')).forEach(function (button) {
            button.addEventListener('click', function () {
                handleProviderClick(button);
            });
        });

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false') {
                closeModal();
            }

            trapFocus(event);
        });
    }());
</script>
</body>
</html>
