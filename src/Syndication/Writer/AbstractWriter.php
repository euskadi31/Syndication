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
namespace Syndication\Writer;

use Syndication\Feed;

abstract class AbstractWriter
{
    /**
     * 
     * @var Syndication\Feed
     */
    protected $feed;

    /**
     * 
     * @param Syndication\Feed $feed
     */
    public function __construct(Feed $feed)
    {
        $this->feed = $feed;
    }

    /**
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}