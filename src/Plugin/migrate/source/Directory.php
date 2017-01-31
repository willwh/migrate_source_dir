<?php

namespace Drupal\migrate_source_directory\Plugin\migrate\source;

use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * Source for a given directory path.
 *
 * @MigrateSource(
 *   id = "directory"
 * )
 */
class Directory extends SourcePluginBase {

  /**
   * Recurse level of directory search.
   *
   * Uses http://php.net/manual/en/recursiveiteratoriterator.setmaxdepth.php.
   *
   * @var int
   */
  protected $recurseLevel = 0;

  /**
   * A list of files from the provided directory, and possible children.
   *
   * @var array
   */
  protected $filesList = [];

  /**
   * An list of file extensions to limit by.
   *
   * @var array
   */
  protected $fileExtensions = [];

  /**
   * An list of directories to search.
   *
   * @var array
   */
  protected $urls = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);

    // Path is required.
    if (!empty($this->configuration['urls'])) {
      foreach ($this->configuration['urls'] as $url) {
        array_push($this->urls, $url);
      }
    }

    // Check for file extenions to limit by.
    if (!empty($this->configuration['file_extensions'])) {
      foreach ($this->configuration['file_extensions'] as $extension) {
        array_push($this->fileExtensions, $extension);
      }
    }

    if ((int) ($this->configuration['recurse_level']) === $this->configuration['recurse_level']) {
      $this->recurseLevel = $this->configuration['recurse_level'];
    }
    else {
      throw new MigrateException('You must declare the \'recurse_level\' as an integer');
    }
  }

  /**
   * Return a string representing the source file path.
   *
   * @return string
   *   The file path.
   */
  public function __toString() {
    return implode(",", $this->urls);
  }

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {
    foreach ($this->urls as $url) {
      $recursive_iter = new \RecursiveDirectoryIterator($url, \FilesystemIterator::SKIP_DOTS);

      // Pass the RecursiveIterator to the constructor of RecursiveIteratorIterator.
      $recursive_iter_iter = new \RecursiveIteratorIterator(
        $recursive_iter, \RecursiveIteratorIterator::SELF_FIRST
      );
      $recursive_iter_iter->setMaxDepth($this->recurseLevel);

      foreach ($recursive_iter_iter as $path => $info) {
        if (!is_dir($path)) {
          $file = pathinfo($path);
          if (!empty($this->fileExtensions)) {
            $ext = $file['extension'];
            if (in_array($ext, $this->fileExtensions)) {
              array_push($this->filesList, [
                'path' => $file['dirname'],
                'filename' => $file['basename'],
                'url' => $path
              ]);
            }
          }
          else {
            array_push($this->filesList, [
              'path' => $file['dirname'],
              'filename' => $file['basename'],
              'url' => $path
            ]);
          }
        }
      }
    }
    return new \ArrayIterator($this->filesList);
  }

  /**
   * We use the full path to the file as the ID.
   */
  public function getIds() {
    $ids = ['url' => ['type' => 'string']];
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'path' => 'The folder path to the file',
      'filename' => 'Filename and extension',
      'url' => 'Full path to the file'
];
    return $fields;
  }

}
