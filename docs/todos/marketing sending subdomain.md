# Marketing Sending Subdomain (Resend)

<!-- todo-check
created_at: 2026-06-21T12:00:00+08:00
ask_after: 2026-07-03T22:29:45+08:00
status: open
-->

Isolate **marketing** email onto its own Resend sending subdomain (e.g. `news.livelatch.com`) so a marketing spam complaint cannot poison **transactional** (auth-code / OTP) deliverability. Transactional stays on `hello@livelatch.com`.

## Checklist

- Verify a second domain/subdomain in Resend for marketing sends; add its DKIM / SPF / DMARC DNS records (Spaceship DNS). Leave root MX on Spacemail.
- Keep root-domain SPF listing **both** Spacemail (human replies) and Resend.
- Route marketing/broadcast sends (and the contact-sync audience) through the marketing subdomain; keep auth codes + service mail on the transactional sender.
- Confirm SPF/DKIM/DMARC pass via mail-tester.com for both senders.

## Owner Follow-Up

- Decide the marketing subdomain name (`news.` / `mail.` / `hello.`).
- Add the DNS records in Spaceship once Resend shows them.
