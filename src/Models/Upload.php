<?php
/**
 * Copyright 2015-2016 Xenofon Spafaridis
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace Phramework\Models;

use \Phramework\Models\Util;
use \Phramework\Exceptions\NotFoundException;

/**
 * Upload class
 *
 * Provides functions for uploading files and images
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @since 0
 * @todo clean up
 * @deprecated since 2.0
 */
class Upload
{
    /**
     * Upload file
     * @param array $parameters The request parameters.
     * @param string $index The paramatern index for example 'file'.
     * @param array $move If $move is set (Is not (null or == array() or false ))
     * then the uploaded file will be moved to the specified directory.
     * The $move takes indexes 'path' and 'name', the path is the directory
     * the uploaded file will be moved. The name is optional and
     * @param array $allowedFiletypes *[Optional] Array with allowed extensions.
     * @param integer $max_size *[Optional] The maximum size of the
     * uploaded file in bytes. Default value is 10485760 bytes
     * @param boolean $parseExtension *[Optional] If true the destination
     * filename will be joined with files extension  Default value is false
     */
    public static function file(
        $file,
        $move = [],
        $allowedFiletypes = ['csv'],
        $max_size = 10485760,
        $parseExtension = false
    ) {
        if (!$file) {
            return 'Select a file';
        }
        $temporaryPath = $file['tmp_name'];
        if (!file_exists($temporaryPath)) {
            throw new NotFoundException('File not found');
        }
        $filename = $file['name'];
        $ext = Util::extension($filename);
        if (!in_array($ext, $allowedFiletypes)) {
            return 'Incorrect file type';
        }
        $size = filesize($temporaryPath);
        if ($size > $max_size) {
            return 'File size exceeds maximum';
        }
        if ($move) {
            if (!is_array($move)) {
                $move['path'] = $move;
            }
            if (isset($move['name']) && $parseExtension) {
                $move['name'] .= '.' . $ext;
            } elseif ($parseExtension) {
                $move['path'] .= '.' . $ext;
            }
            $destination = Util::get_path(
                isset($move['name'])
                ? [$move['path'], $move['name']]
                : [$move['path']]
            );

            if (!rename($temporaryPath, $destination)) {
                return 'Error uploading file';
            }
            return [
                'path' => $destination,
                'name' => basename($destination),
                'size' => filesize($destination),
                'name_original' => basename($file['name'])
            ];
        } else {
            return [
                'path' => $temporaryPath,
                'name' => basename($temporaryPath),
                'size' => filesize($temporaryPath),
                'name_original' => basename($file['name'])
            ];
        }
    }

    /**
     * Upload image
     *
     * @param Object $file Reference to uploaded file
     * @param array $move Move is an array with two indexes,
     * `path` is required and it points the path be stored,
     * `name` is optional and it tells the file name to be stored if not set if
     * will be copied from original upload file name
     * @param array $sizes Is an indexed array
     * @param integer $maxFileSize Maximum file size, Optional by default it's 2MB
     * @param array $allowedFiletypes Array with allowed image extensions,
     * Optional by default it's 'jpg', 'gif', 'png', 'jpeg'
     * @return Array|String If return is not array then it's an error Message,
     * If it's array then every index of the requested Sizes parameter containts the file path of the specific size
     */
    public static function image(
        $file,
        $move = [],
        $sizes = [],
        $maxFileSize = 2097152,
        $allowedFiletypes = [
            'jpg',
            'gif',
            'png',
            'jpeg'
        ]
    ) {
        if (!$file) {
            return 'Select a file';
        }
        $temporaryPath = $file['tmp_name'];
        if (!file_exists($temporaryPath)) {
            throw new NotFoundException('File not found');
        }
        $filename = $file['name'];
        $ext = strtolower(preg_replace('/^.*\.([^.]+)$/D', '$1', $filename));
        if (!in_array($ext, $allowedFiletypes)) {
            return 'Incorrect file type';
        }
        $size = filesize($temporaryPath);
        if ($size > $maxFileSize) {
            return 'File size exceeds maximum';
        }
        $image_info = getimagesize($temporaryPath);
        if (!$image_info) {
            return 'Select an image file';
        }
        if (!$sizes) {
            return 'Sizes not set';
        }
        //Read image
        if ($ext == 'jpg' || $ext == 'jpeg') {
            $src = imagecreatefromjpeg($temporaryPath);
        } elseif ($ext == 'png') {
            $PNG = true;
            $src = imagecreatefrompng($temporaryPath);
        } elseif ($ext == 'gif') {
            $src = imagecreatefromgif($temporaryPath);
        } elseif ($ext == 'bmp') {
            $src = imagecreatefromwbmp($temporaryPath);
        } else {
            return 'Unsupported filetype';
        }

        //Get image dimensions
        $width = imagesx($src);
        $height = imagesy($src);
        $returnArray = [ $name => false];
        
        //Resize for other sizes
        foreach ($sizes as $key => $value) {
            if (!isset($value[0]) || !isset($value[1]) || !is_numeric($value[0]) || !is_numeric($value[1])) {
                continue;
            }

            //Filename
            $destination = \Util::get_path(
                isset($move['name'])
                ? [$move['path'], $move['name']]
                : [$move['path'], \Util::toAscii($filename)]
            );

            //If requires resize
            if ($height > $value[0] || $width > $value[1]) {
                $destinationName = \Util::get_path([ $destinationName, '_' . $key . ($PNG ? '.png' : 'jpg')]);
                $newheight = $value[0];
                $newwidth = $value[1];
                if ($width > $height) {
                    $newheight = ($height / $width) * $value[1];
                } else {
                    $newwidth = ($width / $height) * $value[0];
                }
                $tmp = imagecreatetruecolor($newwidth, $newheight);
                if ($PNG) {
                    //If png mode keep transparency
                    imagealphablending($tmp, false);
                    imagesavealpha($tmp, true);
                    $transparent = imagecolorallocatealpha($tmp, 255, 255, 255, 127);
                    imagefilledrectangle($tmp, 0, 0, $newwidth, $newheight, $transparent);
                }
                imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                if ($PNG) {
                    imagepng($destinationName);
                } else {
                    imagejpeg($destinationName, 100);
                }
                $returnArray[$name . '_' . $key] = $destinationName;
                imagedestroy($tmp);
            } else {
                //Move file without resizing
                //Use the original extension
                $destinationName = \Util::get_path([ $destinationName, '_' . $key . '.' . $ext]);
                copy($temporaryPath, $destinationName);
                $returnArray[$name . '_' . $key] = $destinationName;
            }
        }
        //Destroy image source
        imagedestroy($src);
        //Delete temporary file
        unlink($temporaryPath);
        //Return returnArray
        return $returnArray;
    }

    /**
     * Create a zip archive
     * @param $destination String Zip archive path
     * @param $files array array( array( 'filename' => .. 'path' => .. ) )
     * @param $blobs array array( array( 'filename' => .. 'contents' => .. ) )
     * @todo update zip class namespace
     */
    public static function createZip($destination, $files = [], $blobs = [], $overwrite = true)
    {
        if (!class_exists('ZipArchive')) {
            throw new \Exception('cannot_create_zip_archive');
        }
        //if the zip file already exists and overwrite is false, return false
        if (file_exists($destination) && !$overwrite) {
            return false;
        }
        /* //vars
          $valid_files = array( );
          //if files were passed in...
          if (is_array( $files )) {
          //cycle through each file
          foreach ($files as $file) {
          //make sure the file exists
          if (file_exists( $file )) {
          $valid_files[ ] = $file;
          }
          }
          } */
        //if we have good files...
        // if (count( $valid_files )) {
        //create the archive
        $zip = new ZipArchive();

        if ($zip->open($destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
            return false;
        }
        //Add the files
        foreach ($files as $file) {
            $filename = $blob['filename'];
            $path = $blob['path'];

            if (!file_exists($path)) {
                throw new \Exception('file_NotFoundException');
            }
            $zip->addFile($filename, $path);
        }
        //Add the $blobs
        foreach ($blobs as $blob) {
            $filename = $blob['filename'];
            $contents = $blob['contents'];
            $zip->addFromString($filename, $contents);
        }

        //debug
        //echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
        //close the zip -- done!
        $zip->close();
        //check to make sure the file exists
        return file_exists($destination);
        // } else {
        //     return FALSE;
        //}
    }
}
