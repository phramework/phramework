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

/**
 * Compress class
 *
 * Provides functions to decompress files
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @since 0
 * @todo clean up
 */
class Compress
{
    /**
     * decompress a file archive
     * @param string $compressedFile Archive path
     * @param string $destinationFolder Destination folder to decompress files
     * @param string $originalFilename Original file name
     * @param string $format Select format mode, Default is gz, gz, zip and tar are available.
     * @param string[] $allowedExtensions
     * @return array Returns a list with the decompressed files
     * @throws \Exception
     */
    public static function decompress(
        $compressedFile,
        $destinationFolder,
        $originalFilename = null,
        $format = 'gz',
        $allowedExtensions = [
            'csv',
            'tsv'
        ]
    ) {
        //TODO ADD tar.gz
        $supported_formats = [ 'gz', 'zip', 'tar'];
        if (!in_array($format, $supported_formats)) {
            throw new \Exception('Unsupported compression format');
        }
        switch ($format) {
            case 'gz':
                return self::decompressGz(
                    $compressedFile,
                    $destinationFolder,
                    $originalFilename,
                    $allowedExtensions
                );
            case 'zip':
                return self::decompressZip(
                    $compressedFile,
                    $destinationFolder,
                    $originalFilename,
                    $allowedExtensions
                );
            case 'tar':
                return self::decompressTar(
                    $compressedFile,
                    $destinationFolder,
                    $originalFilename,
                    $allowedExtensions
                );
        }
    }

    /**
     * @throws \Exception
     */
    private static function uncompressZip(
        $compressedFile,
        $destinationFolder,
        $originalFilename = null,
        $allowedExtensions = []
    ) {
        $zip = new \ZipArchive();

        $res = $zip->open($compressedFile);

        if ($res !== true) {
            throw new \Exception('Cannot open zip archive', $res);
        }

        $files = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $info = $zip->statIndex($i);
            $name = $info['name'];
            //Todo check filesize !
            if (in_array(Util::extension($name), $allowedExtensions)) {
                $files[] = $name;
            }
        }
        if (!$files) {
            throw new \Exception('No valid files files found inside archive');
        }
        //TODO CHECK
        $zip->extractTo($destinationFolder, $files);

        $zip->close();

        return $files;
    }

    /**
     * @throws \Exception
     */
    private static function decompressGz(
        $compressedFile,
        $destinationFolder,
        $originalFilename = null,
        $allowedExtensions = []
    ) {
        //IN ORDER TO WORK CORRECTLY GZ FILES MUST HAVE DOUBLE EXTENSION E.G. name.csv.gz (STANDARDIZE IT !)
        //$file_path = Util::getPath(
        //    array($destinationFolder, basename(($originalFilename ? $originalFilename : $compressedFile), '.gz'))
        //);
        //File extension
        $file_path = Util::get_path([
            $destinationFolder,
            basename(($originalFilename ? $originalFilename : $compressedFile), '.gz')
        ]);

        $sfp = gzopen($compressedFile, 'rb');

        if ($sfp === false) {
            throw new \Exception('Cannot open gz archive');
        }

        $fp = fopen($file_path, 'w');

        if ($fp === false) {
            throw new \Exception('Cannot open uncompress');
        }
        while (!gzeof($sfp)) {
            $string = gzread($sfp, 8192);
            fwrite($fp, $string, strlen($string)); //TODO CHECK FOR BINARY FILES
        }
        gzclose($sfp);
        fclose($fp);

        return basename($file_path);
    }

    /**
     * @throws \Exception
     */
    private static function decompressTar(
        $compressedFile,
        $destinationFolder,
        $originalFilename = null,
        $allowedExtensions = []
    ) {
        try {
            $zip = new \PharData($compressedFile);
        } catch (\Exception $e) {
            throw new \Exception('Cannot open tar archive');
        }

        $files = [];

        foreach ($zip as $file) {
            $name = $file->getFileName();
            if (in_array(Util::extension($name), $allowedExtensions)) {
                $files[] = $name;
            }
        }
        if (!$files) {
            throw new \Exception('No valid files found inside archive');
        }

        $zip->extractTo($destinationFolder, $files);

        return $files;
    }
}
