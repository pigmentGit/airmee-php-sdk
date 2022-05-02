<?php
/**
 * @file TimeRange.php
 * @copyright © 2017 Toptal LLC.  Used under license.
 * @author 2017 Melon Software.
 */

namespace Airmee\PhpSdk\Core\Models;

use Airmee\PhpSdk\Core\Exceptions\InvalidArgumentException;

class TimeRange
{
    /** @var \DateTime */
    private $start;

    /** @var \DateTime */
    private $end;

    /** @var string */
    private $formatted;

    /** @var \DateTimeZone */
    private $timezone;

    /**
     * TimeRange constructor.
     * @param int|string|\DateTime $start
     * @param int|string|\DateTime $end
     * @param string $timezone
     * @param string $formatted
     * @throws \Airmee\PhpSdk\Core\Exceptions\InvalidArgumentException
     */
    public function __construct($start, $end, $formatted = null, $timezone = null)
    {
        $this->start = $this->validateDatetime($start, '$start');
        $this->end = $this->validateDatetime($end, '$end');
        $this->timezone = $this->validateTimezone($timezone);

        if ($this->end <= $this->start) {
            throw new InvalidArgumentException('$start must be before $end');
        }

        if ($formatted) {
            $this->formatted = (string)$formatted;
        }
    }

    /**
     * Generate a DateTime from a mixed input
     * @param string|int|\DateTime $datetime
     * @param string $parameterName Only used for exception message
     * @return \DateTime
     * @throws InvalidArgumentException
     */
    private function validateDatetime($datetime, $parameterName)
    {
        if (empty($datetime)) {
            throw new InvalidArgumentException("$parameterName is required");
        } elseif ($datetime instanceof \DateTime) {
            return $datetime;
        } else {
            if ((string)(int)$datetime == $datetime) {
                // An integer or string representation of an integer; treat as a Unix timestamp
                $datetime = '@' . $datetime;
            }
            try {
                $ret = new \DateTime($datetime);
                $ret->setTimezone($this->timezone);

                $errors = \DateTime::getLastErrors();
                if (!empty($errors['warning_count'])) {
                    throw new InvalidArgumentException("$parameterName must be a valid parameter to DateTime($parameterName)", 0);
                }
                return $ret;
            } catch (\Exception $e) {
                throw new InvalidArgumentException("$parameterName must be a valid parameter to DateTime($parameterName)", 0, $e);
            }
        }
    }

    /**
     * Generate a DateTimeZone from a mixed input
     *
     * @param null|\DateTimeZone $timezone If null, use the default timezone 'Europe/Stockholm'
     * @return \DateTimeZone
     */
    public function validateTimezone($timezone)
    {
        if (empty($timezone)) {
            $timezone = new \DateTimeZone('Europe/Stockholm');
        } elseif ($timezone instanceof \DateTimeZone) {
            return $timezone;
        }
        return $timezone;
    }

    /**
     * Get the start of the range
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Get the end of the range
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Get the formatted representation of the range, if one was specified
     * @return \DateTime
     */
    public function getFormatted()
    {
        return $this->formatted;
    }

    /**
     * Get the timezone
     *
     * @return \DateTimeZone
     */
    public function getTimezone()
    {
        return $this->timezone;
    }
}
