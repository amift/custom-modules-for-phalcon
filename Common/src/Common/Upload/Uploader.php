<?php

namespace Common\Upload;

use Common\Upload\Helpers\Format;
use Common\Upload\Validator\FileValidator;
use Phalcon\Http\Request;
use Phalcon\Http\Request\File;

class Uploader
{

    /**
     * @var Request $request
     */
    private $request;

    /**
     * @var File $files
     */
    private $files;

    /**
     * @var FileValidator
     */
    private $validator;

    /**
     * Validation Rules
     *
     * @var array $rules
     */
    private $rules = [];

    /**
     * Uploaded files array
     *
     * @var array $info
     */
    private $info;

    /**
     * Initialize rules
     *
     * @param array $rules
     */
    public function __construct($rules = [])
    {
        if (empty($rules) === false) {
            $this->setRules($rules);
        }

        $this->validator = new FileValidator();
        $this->request = new Request();
    }

    /**
     * Setting up rules for uploaded files
     *
     * @param array $rules
     * @return Uploader
     */
    public function setRules(array $rules)
    {
        foreach ($rules as $key => $values) {
            if ((is_array($values) === true && empty($values) === false) || is_callable($values)) {
                $this->rules[$key] = $values;
            } else {
                $this->rules[$key] = trim($values);
            }
        }

        return $this;
    }

    /**
     * Check if upload files are valid
     *
     * @return bool
     */
    public function isValid()
    {
        $this->files = $this->request->getUploadedFiles();

        if (sizeof($this->files) > 0) {
            foreach ($this->files as $n => $file) {
                foreach ($this->rules as $key => $rule) {
                    if (method_exists($this->validator, 'check' . ucfirst($key)) === true) {
                        $this->validator->{'check' . ucfirst($key)}($file, $rule);
                    }
                }
            }
        }

        $errors = $this->getErrors();

        return (empty($errors) === true) ? true : false;
    }

    public function isValidFile($file)
    {
        foreach ($this->rules as $key => $rule) {
            if (method_exists($this->validator, 'check' . ucfirst($key)) === true) {
                $this->validator->{'check' . ucfirst($key)}($file, $rule);
            }
        }

        $errors = $this->getErrors();

        return (empty($errors) === true) ? true : false;
    }

    /**
     * Check if upload files are valid
     *
     * @return void
     */
    public function move()
    {
        foreach ($this->files as $n => $file) {
            $filename = $file->getName();
            if (isset($this->rules['hash']) === true) {
                if (empty($this->rules['hash']) === true) {
                    $this->rules['hash'] = 'md5';
                }
                if (!is_string($this->rules['hash']) === true) {
                    $filename = call_user_func($this->rules['hash']) . '.' . $file->getExtension();
                } else {
                    $filename = $this->rules['hash']($filename) . '.' . $file->getExtension();
                }
            }
            if (isset($this->rules['customFileName']) && $this->rules['customFileName'] !== '') {
                $filename = $this->rules['customFileName'] . '.' . $file->getExtension();
                $filename = Format::toLatin($filename, '', true);
            }
            if (isset($this->rules['sanitize']) === true) {
                $filename = Format::toLatin($filename, '', true);
            }
            if (isset($this->rules['directory'])) {
                $tmp = rtrim($this->rules['directory'], '/') . DS . $filename;
            } else {
                $tmp = rtrim($this->rules['dynamic'], '/') . DS . $filename;
            }

            // move file to target directory
            $isUploaded = $file->moveTo($tmp);
            if ($isUploaded === true) {
                $this->info[] = [
                    'path'      => $tmp,
                    'directory' => dirname($tmp),
                    'filename'  => $filename,
                    'size'      => $file->getSize(),
                    'extension' => $file->getExtension(),
                    'mime'      => mime_content_type($tmp)
                ];
            }
        }

        return $this->getInfo();
    }

    public function moveFile($file)
    {
        $filename = $file->getName();
        if (isset($this->rules['hash']) === true) {
            if (empty($this->rules['hash']) === true) {
                $this->rules['hash'] = 'md5';
            }
            if (!is_string($this->rules['hash']) === true) {
                $filename = call_user_func($this->rules['hash']) . '.' . $file->getExtension();
            } else {
                $filename = $this->rules['hash']($filename) . '.' . $file->getExtension();
            }
        }
        if (isset($this->rules['customFileName']) && $this->rules['customFileName'] !== '') {
            $filename = $this->rules['customFileName'] . '.' . $file->getExtension();
            $filename = Format::toLatin($filename, '', true);
        }
        if (isset($this->rules['sanitize']) === true) {
            $filename = Format::toLatin($filename, '', true);
        }
        if (isset($this->rules['directory'])) {
            $tmp = rtrim($this->rules['directory'], '/') . DS . $filename;
        } else {
            $tmp = rtrim($this->rules['dynamic'], '/') . DS . $filename;
        }

        // move file to target directory
        $isUploaded = $file->moveTo($tmp);
        if ($isUploaded === true) {
            return [
                [
                    'path'      => $tmp,
                    'directory' => dirname($tmp),
                    'filename'  => $filename,
                    'size'      => $file->getSize(),
                    'extension' => $file->getExtension(),
                    'mime'      => mime_content_type($tmp)
                ]
            ];
        }

        return [];
    }

    /**
     * Return errors messages
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->validator->errors;
    }

    /**
     * Get uploaded files info
     *
     * @return \Phalcon\Session\Adapter\Files
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * Truncate uploaded files
     * 
     * @return void
     */
    public function truncate()
    {
        if (empty($this->info) === false) {
            foreach ($this->info as $n => $file) {
                if (file_exists($file['path'])) {
                    unlink($file['path']);
                }
            }
        }
    }

}
