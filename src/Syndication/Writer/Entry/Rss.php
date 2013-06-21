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
use InvalidArgumentException;
use DateTime;
use Syndication\Uri;
use Syndication\Feed\Entry;

class Rss extends AbstractEntry implements EntryInterface
{
    public function render(XMLWriter $xml)
    {
        $xml->startElement('item');

        $this->_setTitle($xml);
        $this->_setDescription($xml);
        $this->_setDateCreated($xml);
        $this->_setDateModified($xml);
        $this->_setLink($xml);
        $this->_setId($xml);
        $this->_setAuthors($xml);
        $this->_setEnclosure($xml);
        $this->_setCommentLink($xml);
        $this->_setCategories($xml);

        $xml->endElement(); // item
    }

    /**
     * Set entry title
     *
     * @param  XMLWriter $xml
     * @return void
     * @throws InvalidArgumentException
     */
    protected function _setTitle(XMLWriter $xml)
    {
        if (!$this->entry->getDescription()
            && !$this->entry->getTitle()
        ) {
            throw new InvalidArgumentException(
                'RSS 2.0 entry elements SHOULD contain exactly one'
                . ' title element but a title has not been set. In addition, there'
                . ' is no description as required in the absence of a title.'
            );
        }

        $xml->writeElement('title', $this->entry->getTitle());
    }

    /**
     * Set entry description
     *
     * @param  XMLWriter $xml
     * @return void
     * @throws InvalidArgumentException
     */
    protected function _setDescription(XMLWriter $xml)
    {
        if (!$this->entry->getDescription()
            && !$this->entry->getTitle()
        ) {
            throw new InvalidArgumentException(
                'RSS 2.0 entry elements SHOULD contain exactly one'
                . ' description element but a description has not been set. In'
                . ' addition, there is no title element as required in the absence'
                . ' of a description.'
            );
        }

        if (!$this->entry->getDescription()) {
            return;
        }

        $xml->startElement('description');
        $xml->writeCData($this->entry->getDescription());
        $xml->endElement(); // description
    }

    /**
     * Set date entry was last modified
     *
     * @param  XMLWriter $xml
     * @return void
     */
    protected function _setDateModified(XMLWriter $xml)
    {
        if (!$this->entry->getDateModified()) {
            return;
        }

        $xml->writeElement(
            'pubDate', 
            $this->entry->getDateModified()->format(DateTime::RSS)
        );
    }

    /**
     * Set date entry was created
     *
     * @param  XMLWriter $xml
     * @return void
     */
    protected function _setDateCreated(XMLWriter $xml)
    {
        if (!$this->entry->getDateCreated()) {
            return;
        }

        if (!$this->entry->getDateModified()) {
            $this->entry->setDateModified(
                $this->entry->getDateCreated()
            );
        }
    }

    /**
     * Set entry authors
     *
     * @param  XMLWriter $xml
     * @return void
     */
    protected function _setAuthors(XMLWriter $xml)
    {
        $authors = $this->entry->getAuthors();

        if ((!$authors || empty($authors))) {
            return;
        }

        foreach ($authors as $data) {

            $name = $data['name'];

            if (array_key_exists('email', $data)) {
                $name = $data['email'] . ' (' . $data['name'] . ')';
            }

            $xml->writeElement('author', $name);
        }
    }

    /**
     * Set entry enclosure
     *
     * @param  XMLWriter $xml
     * @return void
     * @throws InvalidArgumentException
     */
    protected function _setEnclosure(XMLWriter $xml)
    {
        $data = $this->entry->getEnclosure();

        if ((!$data || empty($data))) {
            return;
        }

        if (!isset($data['type'])) {
            throw new InvalidArgumentException('Enclosure "type" is not set');
        }

        if (!isset($data['length'])) {
            throw new InvalidArgumentException('Enclosure "length" is not set');
        }

        if (isset($data['length']) && (int) $data['length'] <= 0) {
            throw new InvalidArgumentException(
                'Enclosure "length" must be an integer'
                . ' indicating the content\'s length in bytes'
            );
        }

        $xml->startElement('enclosure');
        $xml->writeAttribute('type', $data['type']);
        $xml->writeAttribute('length', $data['length']);
        $xml->writeAttribute('url', $data['uri']);
        $xml->endElement();
    }

    /**
     * Set link to entry
     *
     * @param  XMLWriter $xml
     * @return void
     */
    protected function _setLink(XMLWriter $xml)
    {
        if (!$this->entry->getLink()) {
            return;
        }

        $xml->writeElement('link', $this->entry->getLink());
    }

    /**
     * Set entry identifier
     *
     * @param  XMLWriter $xml
     * @return void
     */
    protected function _setId(XMLWriter $xml)
    {
        if (!$this->entry->getId() && !$this->entry->getLink()) {
            return;
        }

        if (!$this->entry->getId()) {
            $this->entry->setId(
                $this->entry->getLink()
            );
        }

        $xml->startElement('guid');

        if (!Uri::factory($this->entry->getId())->isValid()) {
            $xml->writeAttribute('isPermaLink', 'false');
        }

        $xml->text($this->entry->getId());
        $xml->endElement(); // guid
    }

    /**
     * Set link to entry comments
     *
     * @param  XMLWriter $xml
     * @return void
     */
    protected function _setCommentLink(XMLWriter $xml)
    {
        $link = $this->entry->getCommentLink();

        if (!$link) {
            return;
        }

        $xml->writeElement('comments', $link);
    }

    /**
     * Set entry categories
     *
     * @param XMLWriter $xml
     * @return void
     */
    protected function _setCategories(XMLWriter $xml)
    {
        $categories = $this->entry->getCategories();

        if (!$categories) {
            return;
        }

        foreach ($categories as $cat) {

            $xml->startElement('category');

            if (isset($cat['scheme'])) {
                $xml->writeAttribute('domain', $cat['scheme']);
            }
            
            $xml->writeCData($cat['term']);
            $xml->endElement(); // category
        }
    }
}
