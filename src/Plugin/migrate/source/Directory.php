<?php

namespace Drupal\migrate_source_dir\Plugin\migrate\source;

use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * Source for a given directory path.
 *
 * @MigrateSource(
 *   id = "dir"
 * )
 */
class Directory extends SourcePluginBase {

  /**
   * Recurse through a provided directory.
   *
   * @var bool
   */
  protected $recurse = FALSE;

  /**
   * A list of files from the provided directory, and possible children
   *
   * @var array
   */
  protected $files_list = [];



  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);

    // Path is required.
    if (empty($this->configuration['path'])) {
      throw new MigrateException('You must declare the "path" to search for files in your source settings.');
    }

    // Ensure the path is valid
    if (!file_exists($this->configuration['path'])) {
      throw new MigrateException('You must provide a valid system path to search for files.');
    }

    if ($this->configuration['recurse']) {
      $this->recurse = TRUE;
    }
  }

  /**
   *
   */

  /**
   * Return a string representing the source file path.
   *
   * @return string
   *   The file path.
   */
  public function __toString() {
    return implode(",", $this->files_list);
  }

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {
    if (!$this->recurse) {
      $it = new \DirectoryIterator($this->configuration['path']);
      foreach ($it as $fileinfo) {
        if (!$fileinfo->isDot() && !$fileinfo->isDir()) {
          $path = $fileinfo->getPath();
          $filename = $fileinfo->getFilename();
          $pathname = $path . '/' . $filename;

          if (!empty($this->configuration['file_ext'])) {
            if ($fileinfo->getExtension() == $this->configuration['file_ext']) {
              array_push($this->files_list, [
                'path' => $path,
                'filename' => $filename,
                'pathname' => $pathname
              ]);
            }
          } else {
            array_push($this->files_list, [
              'path' => $path,
              'filename' => $filename,
              'pathname' => $pathname
            ]);
          }
        }
      }
    } else {
      // This needs a cleanup

      $recursive_iter = new \RecursiveDirectoryIterator($this->configuration['path'], \FilesystemIterator::SKIP_DOTS);

      // Pass the RecursiveIterator to the constructor of RecursiveIteratorIterator.
      $recursive_iter_iter = new \RecursiveIteratorIterator(
        $recursive_iter, \RecursiveIteratorIterator::SELF_FIRST
      );

      foreach ($recursive_iter_iter as $path => $info) {
        if (!is_dir($path)) {
          $file = pathinfo($path);
          if (!empty($this->configuration['file_ext'])) {
            $ext = $file['extension'];
            if ($ext == $this->configuration['file_ext']) {
              array_push($this->files_list, ['path' => $file['dirname'], 'filename' => $file['basename'], 'pathname' => $path]);
            }
          } else {
            array_push($this->files_list, ['path' => $file['dirname'], 'filename' => $file['basename'], 'pathname' => $path]);
          }
        }
      }
    }
    return new \ArrayIterator($this->files_list);
  }

  /**
   * We use the full path to the file as the ID.
   *
   */
  public function getIDs() {
    $ids = ['pathname' => ['type' => 'string']];
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = ['path', 'filename', 'pathname'];
    return $fields;
  }
}
