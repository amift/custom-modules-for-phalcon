<?php

namespace Common\Upload\Validator;

use Common\Upload\Helpers\Message;
use Common\Upload\Helpers\Format;
use Phalcon\Http\Request\File;

class FileValidator
{

    /**
     * Error message container
     * 
     * @var array $errors
     */
    public $errors = [];

    /**
     * Check minimum file size
     *
     * @param File $file
     * @param $value
     * @return bool
     */
    public function checkMinsize(File $file, $value)
    {
        if (is_array($value) === true) {
            $value = $value[key($value)];
        }

        if ($file->getSize() < (int) $value) {
            $this->errors['INVALID_MIN_SIZE'] = sprintf(Message::get('INVALID_MIN_SIZE'), $file->getName(), Format::bytes($value));
            return false;
        }

        return true;
    }

    /**
     * Check maximum file size
     *
     * @param File $file
     * @param mixed $value
     * @return bool
     */
    public function checkMaxsize(File $file, $value)
    {
        if (is_array($value) === true) {
            $value = $value[key($value)];
        }

        if ($file->getSize() > (int) $value) {
            $this->errors['INVALID_MAX_SIZE'] = sprintf(Message::get('INVALID_MAX_SIZE'), $file->getName(), Format::bytes($value));
            return false;
        }

        return true;
    }

    /**
     * Check file allowed extensions
     *
     * @param File $file
     * @param mixed $value
     * @return bool
     */
    public function checkExtensions(File $file, $value)
    {
        if (is_array($value) === false) {
            $value = [$value];
        }

        if ($file->getTempName() == '') {
            $this->errors['INVALID_NO_FILE'] = sprintf(Message::get('INVALID_NO_FILE'));
            return false;
        }

        if (in_array(strtolower($file->getExtension()), $value) === false) {
            $this->errors['INVALID_EXTENSION'] = sprintf(Message::get('INVALID_EXTENSION'), $file->getName(), implode(',', $value));
            return false;
        }

        return true;
    }

    /**
     * Check file allowed extensions
     *
     * @param File $file
     * @param mixed $value
     * @return bool
     */
    public function checkMimes(File $file, $value)
    {
        if (is_array($value) === false) {
            $value = [$value];
        }

        if ($file->getTempName() == '') {
            $this->errors['INVALID_NO_FILE'] = sprintf(Message::get('INVALID_NO_FILE'));
            return false;
        }

        if (in_array($file->getRealType(), $value) === false) {
            $this->errors['INVALID_MIME_TYPES'] = sprintf(Message::get('INVALID_MIME_TYPES'), $file->getName(), implode(',', $value));
            return false;
        }

        return true;
    }

    /**
     * Check upload directory
     *
     * @param null|File $file
     * @param mixed $value
     * @param $value
     * @return bool
     */
    public function checkDirectory(File $file = null, $value)
    {
        if (is_array($value) === true) {
            $value = $value[key($value)];
        }

        if (file_exists($value) === false) {
            $this->errors['INVALID_UPLOAD_DIR'] = sprintf(Message::get('INVALID_UPLOAD_DIR'), $value);
            return false;
        }

        if (is_writable($value) === false) {
            $this->errors['INVALID_PERMISSION_DIR'] = sprintf(Message::get('INVALID_PERMISSION_DIR'), $value);
            return false;
        }

        return true;
    }

    /**
     * Create Directory if not exist
     *
     * @param null|File $file
     * @param string $directory
     * @param int $permission
     * @return bool
     */
    public function checkDynamic(File $file = null, $directory, $permission = 0777)
    {
        if (is_dir($directory) === false && file_exists($directory) === false) {
            mkdir(rtrim($directory,'/') . DS, $permission, true);
        }

        return true;
    }

}
