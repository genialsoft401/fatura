# Cron de notificações (faturas / plano / password)

## Por que tabela e não AsyncStorage

O AsyncStorage é armazenamento **local ao dispositivo**, usado dentro de uma
app React Native — só a própria app, no próprio telemóvel, consegue ler o
que lá está. Um cron corre no **servidor**, sem qualquer ligação a um
dispositivo específico do utilizador, por isso nunca teria acesso a essa
informação. Além disso, com AsyncStorage:

- perdes o histórico se o utilizador desinstalar a app ou trocar de telemóvel;
- não há como saber, do lado do servidor, se uma notificação já foi enviada
  (arriscas duplicar avisos a cada execução do cron);
- não dá para auditar tudo o que foi enviado a um cliente.

Por isso a tabela `notifications` é a fonte de verdade. Uma app mobile pode
opcionalmente guardar em AsyncStorage apenas o estado "lido/não lido" *depois*
de consultar a API — isso sim é local e efémero.

## Instalação

```bash
mysql -u root -p u523793545_sistema_fatura < sql/001_create_notifications_table.sql
mysql -u root -p u523793545_sistema_fatura < sql/002_add_password_changed_at.sql
```

No endpoint de "alterar password" da tua aplicação, atualiza também
`password_changed_at = NOW()` e `is_active = 1` (ver comentário no
ficheiro `002_add_password_changed_at.sql`).

Define as variáveis de ambiente (ou edita `config.php` diretamente):

```
DB_HOST, DB_NAME, DB_USER, DB_PASS
MAIL_FROM, MAIL_FROM_NAME
SMTP_HOST, SMTP_USER, SMTP_PASS, SMTP_PORT   (se usares PHPMailer/SMTP)
```

## Agendar no crontab

```bash
crontab -e
```

Correr a cada hora (recomendado, já que a idempotência evita duplicados):

```
0 * * * * /usr/bin/php /caminho/para/cron/notify_cron.php >> /caminho/para/cron/cron.log 2>&1
```

Ou uma vez por dia, às 08:00:

```
0 8 * * * /usr/bin/php /caminho/para/cron/notify_cron.php >> /caminho/para/cron/cron.log 2>&1
```

## O que o ciclo faz a cada execução

1. **Faturas** — avisa o contacto quando a fatura vence dentro de N dias
   (`invoice_due_soon`), e repete o aviso semanalmente enquanto estiver
   vencida e por pagar (`invoice_overdue`).
2. **Subscrições/plano** — avisa o(s) owner/admin da empresa quando a
   subscrição está prestes a terminar; quando expira, atualiza
   automaticamente `subscriptions.status` e `companies.plan_status` para
   `expired` e notifica; e avisa sobre pagamentos (`subscription_orders`)
   pendentes há mais de X horas.
3. **Password** — quando deteta `password_changed_at` recente, garante
   `is_active = 1` (rede de segurança caso o endpoint da app falhe) e
   envia email de confirmação de segurança.

Cada notificação só é enviada **uma vez** por evento, graças à coluna
`notify_key` (UNIQUE) — se o cron correr várias vezes antes do evento mudar,
não duplica envios.

## Extensões fáceis

- Trocar `mail()` por PHPMailer/SMTP em `src/Mailer.php`.
- Adicionar canal SMS/push: já existe a coluna `channel` na tabela.
- Adicionar uma tela de "notificações" na app: basta consultar
  `SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC`.