<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Database saving file helper
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Helper_File_Storage_Database extends Mage_Core_Helper_Abstract
{
    /**
     * Database storage model
     * @var null|Mage_Core_Model_File_Storage_Database
     */
    protected $_databaseModel = null;

    /**
     * Storage resource model
     * @var null|Mage_Core_Model_Mysql4_File_Storage_Database
     */
    protected $_resourceModel = null;

    /**
     * Db usage flag
     *
     * @var bool
     */
    protected $_useDb = null;

    /**
     * Check if we use DB storage
     * Note: Disabled as not completed feature
     *
     * @return bool
     */
    public function checkDbUsage()
    {
        if (is_null($this->_useDb)) {
            $currentStorage = (int) Mage::app()
                ->getConfig()->getNode(Mage_Core_Model_File_Storage::XML_PATH_STORAGE_MEDIA);
            $this->_useDb = ($currentStorage == Mage_Core_Model_File_Storage::STORAGE_MEDIA_DATABASE);
        }

        return $this->_useDb;
    }

    /**
     * Get database storage model
     *
     * @return Mage_Core_Model_File_Storage_Database
     */
    public function getStorageDatabaseModel()
    {
        if (is_null($this->_databaseModel)) {
            $this->_databaseModel = Mage::getModel('core/file_storage_database');
        }

        return $this->_databaseModel;
    }

    /**
     * Get file storage model
     *
     * @return Mage_Core_Model_File_Storage_File
     */
    public function getStorageFileModel()
    {
        return Mage::getSingleton('core/file_storage_file');
    }

    /**
     * Get storage model
     *
     * @return Mage_Core_Model_Mysql4_File_Storage_Database
     */
    public function getResourceStorageModel()
    {
        if (is_null($this->_resourceModel)) {
            $this->_resourceModel = $this->getStorageDatabaseModel()->getResource();
        }
        return $this->_resourceModel;
    }

    /**
     * Save file in DB storage
     *
     * @param string $filename
     */
    public function saveFile($filename)
    {
        if ($this->checkDbUsage()) {
            $this->getStorageDatabaseModel()->saveFile($this->_removeAbsPathFromFileName($filename));
        }
    }

    /**
     * Rename file in DB storage
     *
     * @param string $oldName
     * @param string $newName
     */
    public function renameFile($oldName, $newName) {
        if ($this->checkDbUsage()) {
            $this->getStorageDatabaseModel()
                ->renameFile($this->_removeAbsPathFromFileName($oldName), $this->_removeAbsPathFromFileName($newName));
        }
    }

    /**
     * Copy file in DB storage
     *
     * @param string $oldName
     * @param string $newName
     */
    public function copyFile($oldName, $newName) {
        if ($this->checkDbUsage()) {
            $this->getStorageDatabaseModel()
                ->copyFile($this->_removeAbsPathFromFileName($oldName), $this->_removeAbsPathFromFileName($newName));
        }
    }

    /**
     * Check whether file exists in DB
     *
     * @param string $filename can be both full path or partial (like in DB)
     * @return bool|null
     */
    public function fileExists($filename)
    {
        if ($this->checkDbUsage()) {
            return $this->getStorageDatabaseModel()->fileExists($this->_removeAbsPathFromFileName($filename));
        } else {
            return null;
        }
    }

    /**
     * Get unique name for passed file in case this file already exists
     *
     * @param string $directory - can be both full path or partial (like in DB)
     * @param string $filename - not just a filename. Can have directory chunks. return will have this form
     * @return string
     */
    public function getUniqueFilename($directory, $filename)
    {
        if ($this->checkDbUsage()) {
           $directory = $this->_removeAbsPathFromFileName($directory);
            if($this->fileExists($directory . $filename)) {
                $index = 1;
                $extension = strrchr($filename, '.');
                $filenameWoExtension = substr($filename, 0, -1 * strlen($extension));
                while($this->fileExists($directory . $filenameWoExtension . '_' . $index . $extension)) {
                    $index ++;
                }
                $filename = $filenameWoExtension . '_' . $index . $extension;
            }
        }
        return $filename;
    }

    /**
     * Save database file to file system
     *
     * @param string $filename
     * @return bool|int
     */
    public function saveFileToFilesystem($filename) {
        if ($this->checkDbUsage()) {
            $file = Mage::getModel('core/file_storage_database')->
                loadByFilename($this->_removeAbsPathFromFileName($filename));
            if (!$file->getId()) {
                return false;
            }

            return $this->getStorageFileModel()->saveFile($file, true);
        }
    }

    /**
     * Return relative uri for media content by full path
     *
     * @param string $fullPath
     * @return string
     */
    public function getMediaRelativePath($fullPath)
    {
        $relativePath = ltrim(str_replace(Mage::getBaseDir('media'), '', $fullPath), DS);
        return str_replace(DS, '/', $relativePath);
    }

    /**
     * Deletes from DB files, which belong to one folder
     *
     * @param string $folderName
     */
    public function deleteFolder($folderName)
    {
        if ($this->checkDbUsage()) {
            $this->getResourceStorageModel()->deleteFolder($this->_removeAbsPathFromFileName($folderName));
        }
    }

    /**
     * Deletes from DB files, which belong to one folder
     *
     * @param string $filename
     */
    public function deleteFile($filename)
    {
        if ($this->checkDbUsage()) {
            $this->getStorageDatabaseModel()->deleteFile($this->_removeAbsPathFromFileName($filename));
        }
    }

    /**
     * Saves uploaded by Varien_File_Uploader file to DB with existence tests
     *
     * param $result should be result from Varien_File_Uploader::save() method
     * Checks in DB, whether uploaded file exists ($result['file'])
     * If yes, renames file on FS (!!!!!)
     * Saves file with unique name into DB
     * If passed file exists returns new name, file was renamed to (in the same context)
     * Otherwise returns $result['file']
     *
     * @param array $result
     * @return string
     */
    public function saveUploadedFile($result = array())
    {
        if ($this->checkDbUsage()) {
            $path = str_replace('/', DS, $result['path']);
            if (!in_array(substr($result['file'], 0, 1), array('/', DS))
                && !in_array(substr($result['path'], -1), array('/', DS))) {
                $path = $path . '/';
            }
            $uniqueResultFile = $this->getUniqueFilename($path, $result['file']);
            if ($uniqueResultFile !== $result['file']) {
                $ioObject = new Varien_Io_File();
                $ioObject->open(array('path' => $path));
                $ioObject->mv($path . $result['file'], $path . $uniqueResultFile);
            }
            $this->saveFile($path . $uniqueResultFile);

            return $uniqueResultFile;
        } else {
            return $result['file'];
        }
    }

    /**
     * Convert full filepath to local (as used by model)
     * If not - returns just a filename
     *
     * @param string $filename
     * @return string
     */
    protected function _removeAbsPathFromFileName($filename)
    {
        return $this->getMediaRelativePath($filename);
    }

    /**
     * Return Media base dir
     *
     * @return string
     */
    public function getMediaBaseDir()
    {
        return rtrim(Mage::getBaseDir('media'), DS);
    }
}
