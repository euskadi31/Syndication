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

use XMLWriter;
use Syndication\Feed\Entry;

interface EntryInterface
{
    /**
     * 
     * @param Syndication\Feed\Entry $entry
     */
    public function __construct(Entry $entry);

    /**
     * Render entry
     * 
     * @param  XMLWriter $xml
     * @return void
     */
    public function render(XMLWriter $xml);
}