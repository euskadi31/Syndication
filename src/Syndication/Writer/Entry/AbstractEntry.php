<?php
/**
 * @package Syndication
 * @author Axel Etcheverry <axel@etcheverry.biz>
 * @copyright Copyright (c) 2013 Axel Etcheverry (http://www.axel-etcheverry.com)
 * Displays <a href="http://creativecommons.org/licenses/MIT/deed.fr">MIT</a>
 * @license http://creativecommons.org/licenses/MIT/deed.fr MIT
 */

/**
 * @namespace
 */
namespace Syndication\Writer\Entry;

use Syndication\Feed\Entry;

abstract class AbstractEntry
{
    /**
     * 
     * @var Syndication\Feed\Entry
     */
    protected $entry;

    /**
     * Encoding of all text values
     *
     * @var string
     */
    protected $encoding = 'UTF-8';

    /**
     * 
     * @param Syndication\Feed\Entry $entry
     */
    public function __construct(Entry $entry)
    {
        $this->entry = $entry;

        $this->setEncoding($entry->getEncoding());
    }

    /**
     * Set feed encoding
     *
     * @param  string $encoding
     * @return Syndication\Writer\Entry\AbstractEntry
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;

        return $this;
    }

    /**
     * Get feed encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }
}

