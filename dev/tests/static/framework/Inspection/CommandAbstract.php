<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento
 * @subpackage  static_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Abstract shell command for the static code inspection
 */
abstract class Inspection_CommandAbstract
{
    /**
     * @var string
     */
    protected $_reportFile;

    /**
     * Constructor
     *
     * @param string $reportFile Destination file to write inspection report to
     */
    public function __construct($reportFile)
    {
        $this->_reportFile = $reportFile;
    }

    /**
     * Build and execute the shell command
     *
     * @param array $whiteList Files/directories to be inspected
     * @param array $blackList Files/directories to be excluded from the inspection
     * @return bool
     */
    public function run(array $whiteList, array $blackList = array())
    {
        $shellCmd = $this->_buildShellCmd($whiteList, $blackList);
        return ($this->_execShellCmd($shellCmd) !== false);
    }

    /**
     * Whether the command can be ran on the current environment
     *
     * @return bool
     */
    public function canRun()
    {
        return ($this->_execShellCmd($this->_buildVersionShellCmd()) !== false);
    }

    /**
     * Retrieve the shell command version
     *
     * @return string|null
     */
    public function getVersion()
    {
        $versionOutput = $this->_execShellCmd($this->_buildVersionShellCmd());
        if (!$versionOutput) {
            return null;
        }
        return (preg_match('/[^\d]*([^\s]+)/', $versionOutput, $matches) ? $matches[1] : $versionOutput);
    }

    /**
     * Get path to the report file
     *
     * @return string
     */
    public function getReportFile()
    {
        return $this->_reportFile;
    }

    /**
     * Build the shell command that outputs the version
     *
     * @return string
     */
    abstract protected function _buildVersionShellCmd();

    /**
     * Build the valid shell command
     *
     * @param array $whiteList
     * @param array $blackList
     * @return string
     */
    abstract protected function _buildShellCmd($whiteList, $blackList);

    /**
     * Execute a shell command on the current environment and return its output or FALSE on failure
     *
     * @param string $shellCmd
     * @return string|false
     */
    protected function _execShellCmd($shellCmd)
    {
        $output = array();
        exec($shellCmd . ' 2>&1', $output, $exitCode);
        $output = implode(PHP_EOL, $output);
        return ($exitCode === 0 ? $output : false);
    }
}
