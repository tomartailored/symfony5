<?php

namespace App\Services;

use \Symfony\Component\HttpFoundation\File\UploadedFile;
use \Symfony\Component\DependencyInjection\ContainerInterface;

class FileUploader {
    
    /**
     * @var $_container;
     */
    private $_container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->_container = $container;
    }
    
    public function uploadFile(UploadedFile $file)
    {
        $filename = md5(uniqid()) . '.' . $file->guessClientExtension();
        $file->move(
            $this->_container->getParameter('logos_dir'),
            $filename
        );
        
        return $filename;
    }
}

