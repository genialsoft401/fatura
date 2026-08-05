<?php

declare(strict_types=1);

namespace Cron;

class NotificationRepository
{
    public function __construct(private \PDO $pdo, private Mailer $mailer) {}

    /**
     * Regista + envia uma notificação, de forma idempotente.
     * Se $notifyKey já existir na tabela, a notificação é ignorada
     * (evita reenviar o mesmo aviso a cada execução do cron).
     */
    public function notifyOnce(
        int $companyId,
        ?int $userId,
        string $type,
        string $referenceType,
        int $referenceId,
        string $notifyKey,
        string $recipient,
        string $subject,
        string $message
    ): bool {
        $stmt = $this->pdo->prepare(
            'INSERT INTO notifications
                (company_id, user_id, type, reference_type, reference_id, notify_key,
                 channel, recipient, subject, message, status, attempts)
             VALUES
                (:company_id, :user_id, :type, :reference_type, :reference_id, :notify_key,
                 "email", :recipient, :subject, :message, "pending", 0)'
        );

        try {
            $stmt->execute([
                ':company_id'      => $companyId,
                ':user_id'         => $userId,
                ':type'            => $type,
                ':reference_type'  => $referenceType,
                ':reference_id'    => $referenceId,
                ':notify_key'      => $notifyKey,
                ':recipient'       => $recipient,
                ':subject'         => $subject,
                ':message'         => $message,
            ]);
        } catch (\PDOException $e) {
            // Código 23000 = violação de UNIQUE (notify_key já existe) -> já foi notificado
            if ($e->getCode() === '23000') {
                return false;
            }
            throw $e;
        }

        $id = (int)$this->pdo->lastInsertId();
        $sent = $this->mailer->send($recipient, $subject, $message);

        $this->pdo->prepare(
            'UPDATE notifications
             SET status = :status, attempts = attempts + 1, sent_at = :sent_at
             WHERE id = :id'
        )->execute([
            ':status'  => $sent ? 'sent' : 'failed',
            ':sent_at' => $sent ? date('Y-m-d H:i:s') : null,
            ':id'      => $id,
        ]);

        return $sent;
    }
}
