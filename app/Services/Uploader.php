<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;

/**
 * Téléversement d'images sécurisé : MIME réel, taille, dimensions, nom aléatoire généré côté serveur.
 */
final class Uploader
{
    public const DIR = '/public/uploads/projects';

    /** @return string nom de fichier stocké (ex. 3f2a....jpg) */
    public static function saveImage(array $file): string
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
            throw new \RuntimeException('Le fichier dépasse la taille maximale autorisée.');
        }
        if ($error !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Le téléversement a échoué. Veuillez réessayer.');
        }
        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new \RuntimeException('Fichier invalide.');
        }
        if ((int) ($file['size'] ?? 0) > (int) Config::get('upload.max_bytes')) {
            throw new \RuntimeException('Le fichier dépasse la taille maximale autorisée.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($tmp);
        $allowed = (array) Config::get('upload.mimes');
        if (!isset($allowed[$mime])) {
            throw new \RuntimeException('Format non autorisé (JPEG, PNG ou WebP uniquement).');
        }
        $info = @getimagesize($tmp);
        if ($info === false || ($info['mime'] ?? '') !== $mime) {
            throw new \RuntimeException('Le fichier n’est pas une image valide.');
        }
        $max = (int) Config::get('upload.max_dimension');
        if ($info[0] > $max || $info[1] > $max) {
            throw new \RuntimeException('Dimensions trop grandes (' . $max . ' px maximum).');
        }

        $dir = BASE_PATH . self::DIR;
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException('Dossier de téléversement inaccessible.');
        }
        $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
        if (!@move_uploaded_file($tmp, $dir . '/' . $name)) {
            throw new \RuntimeException('Impossible d’enregistrer le fichier.');
        }
        @chmod($dir . '/' . $name, 0644);
        return $name;
    }

    public static function delete(?string $name): void
    {
        if ($name !== null && self::isValidName($name)) {
            $path = BASE_PATH . self::DIR . '/' . $name;
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    public static function isValidName(string $name): bool
    {
        return (bool) preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/', $name);
    }
}
