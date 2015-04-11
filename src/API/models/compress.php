<?php

namespace Phramework\API\models;

use Phramework\API\models\util;

/**
 * Compress class
 * 
 * Provides functions to uncompress files
 * 
 * @since 0
 * @category models
 * @package API
 */
class compress {

    /**
     * Uncompress file archive
     * @param String $compressed_file Archive path
     * @param String $destination_folder Destination folder to uncompress files
     * @param String $original_filename Original file name
     * @param String $format Select format mode, Default is gz, gz, zip and tar are available.
     * @return Array Returns a list with the uncompresed files
     */
    public static function uncompress($compressed_file, $destination_folder, $original_filename = NULL, $format = 'gz', $allowed_extensions = [ 'csv', 'tsv']) {
        //TODO ADD tar.gz
        $supported_formats = [ 'gz', 'zip', 'tar'];
        if (!in_array($format, $supported_formats)) {
            throw new \Exception('Unsupported comression format');
        }
        switch ($format) {
            case 'gz':
                return self::uncompress_gz($compressed_file, $destination_folder, $original_filename, $allowed_extensions);
            case 'zip':
                return self::uncompress_zip($compressed_file, $destination_folder, $original_filename, $allowed_extensions);
            case 'tar':
                return self::uncompress_tar($compressed_file, $destination_folder, $original_filename, $allowed_extensions);
        }
    }

    private static function uncompress_zip($compressed_file, $destination_folder, $original_filename = NULL, $allowed_extensions = []) {
        $zip = new \ZipArchive();

        $res = $zip->open($compressed_file);

        if ($res !== TRUE) {
            throw new \API\exceptions\request('Cannot open zip archive', $res);
        }

        $files = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $info = $zip->statIndex($i);
            $name = $info['name'];
            //Todo check filesize !
            if (in_array(util::extension($name), $allowed_extensions)) {
                $files[] = $name;
            }
        }
        if (!$files) {
            throw new \Exception('No valid files files found inside archive');
        }
        //TODO CHECK
        $zip->extractTo($destination_folder, $files);

        $zip->close();

        return $files;
    }

    private static function uncompress_gz($compressed_file, $destination_folder, $original_filename = NULL, $allowed_extensions = []) {
        //IN ORDER TO WORK CORRECTLY GZ FILES MUST HAVE DOUBLE EXTENSION E.G. name.csv.gz ( STANDARIZE IT ! )
        //$file_path = util::get_path( array( $destination_folder, basename( ( $original_filename ? $original_filename : $compressed_file ), '.gz' ) ) );
        //File extension
        $file_path = util::get_path([ $destination_folder, basename(( $original_filename ? $original_filename : $compressed_file), '.gz')]);

        $sfp = gzopen($compressed_file, "rb");

        if ($sfp === FALSE) {
            throw new \Exception('Cannot open gz archive');
        }

        $fp = fopen($file_path, "w");

        if ($fp === FALSE) {
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

    private static function uncompress_tar($compressed_file, $destination_folder, $original_filename = NULL, $allowed_extensions = []) {

        try {
            $zip = new PharData($compressed_file);
        } catch (\UnexpectedValueException $e) {
            throw new \API\exceptions\request('Cannot open tar archive');
        }

        $files = [];

        foreach ($zip as $file) {
            $name = $file->getFileName();
            if (in_array(util::extension($name), $allowed_extensions)) {
                $files[] = $name;
            }
        }
        if (!$files) {
            throw new \Exception('No valid files found inside archive');
        }

        $zip->extractTo($destination_folder, $files);

        return $files;
    }

}