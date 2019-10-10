<?php
/**
 * Interface VersionManagerInterface
 */
interface VersionManagerInterface
{
    /**
     * method is used to increase the major version and reset minor and patch values
     */
    public function major();

    /**
     * method is used to increase the minor version and reset  patch value
     */
    public function minor();

    /**
     * method is used to increase the patch value
     */
    public function patch();

    /**
     * @return string
     */
    public function release() :string;

    /**
     * method is used to undo a previous version change.
     * We can only rollback as much changes as we did.
     * Obviously, we can't rollback a version if we didn't make any changes.
     */
    public function rollback();
}


class VersionManagerException extends \Exception {
    /**
     * VersionManagerParserException constructor.
     */
    public function __construct()
    {
        parent::__construct('Cannot rollback!');
    }
}
/**
 * Class VersionManagerParserException
 */
class VersionManagerParserException extends \Exception {
    /**
     * VersionManagerParserException constructor.
     */
    public function __construct()
    {
        parent::__construct('Error occurred while parsing version!');
    }
}

final class VersionManagerParser
{
    /**
     * @param string $string
     *
     * @return array
     *
     * @throws VersionManagerParserException
     */
    public static function parse(string $string) : array
    {
        $args = explode('.', $string);

        if (count($args) < 3) {
            throw new VersionManagerParserException();
        }

        for ($i = 0;  $i < 3; $i++) {
            if (!intval($args[$i]) && $args[$i] !== '0') {
                throw new VersionManagerParserException();
            }

            $args[$i] = (int) $args[$i];
        }

        return array_slice($args, 0,3);
    }
}


final class VersionManager implements VersionManagerInterface
{
    /**
     * @var VersionManager
     */
    private $rollbackVersion;

    /**
     * @var int
     */
    private $majorVal = 0;

    /**
     * @var int
     */
    private $minorVal = 0;

    /**
     * @var int
     */
    private $pathVal = 1;

    /**
     * @return string
     */
    private function getVersion() :string {
        return implode([$this->majorVal, $this->minorVal, $this->pathVal], '.');
    }

    /**
     * VersionManager constructor.
     *
     * @param string|null $version
     *
     * @throws VersionManagerParserException
     */
    public function __construct(string $version = null)
    {

        if ($version) {
            $args = VersionManagerParser::parse($version);

            $this->majorVal = array_shift($args);
            $this->minorVal = array_shift($args);
            $this->pathVal = array_shift($args);
        }
    }

    /**
     * @return string
     */
    public function release() :string
    {
        return $this->getVersion();
    }

    /**
     * @return $this
     */
    public function major()
    {
        $this->rollbackVersion = clone $this;

        $this->majorVal++;
        $this->minorVal = 0;
        $this->pathVal = 0;

        return $this;
    }

    /**
     * @return VersionManager
     */
    public function minor()
    {
        $this->rollbackVersion = clone $this;

        $this->minorVal++;
        $this->pathVal = 0;

        return $this;
    }

    /**
     * @return $this
     */
    public function patch()
    {
        $this->rollbackVersion = clone $this;

        $this->pathVal++;

        return $this;
    }

    /**
     * @return VersionManager
     *
     * @throws VersionManagerException
     */
    public function rollback()
    {
        if (!$this->rollbackVersion) {
            throw new VersionManagerException();
        }
        return $this->rollbackVersion;
    }
}

/**$vm = new VersionManager('1.5.007');

$vm->major()->minor()->patch();
die(var_dump($vm->rollback()->rollback()->release()));**/