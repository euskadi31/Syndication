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

interface WriterInterface
{
    /**
     * 
     * @param Syndication\Feed $feed
     */
    public function __construct(Feed $feed);

    /**
     * Write xml document
     * 
     * @return string
     */
    public function render();

    /**
     * 
     * @return string
     */
    public function __toString();
}