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
use DOMDocument;
use Syndication\Uri;
use Syndication\Feed\Entry;

class Atom extends AbstractEntry implements EntryInterface
{
    public function render(XMLWriter $xml)
    {
        $xml->startElement('entry');

        $this->_setTitle($xml);
        $this->_setDescription($xml);
        $this->_setDateCreated($xml);
        $this->_setDateModified($xml);
        $this->_setLink($xml);
        $this->_setId($xml);
        $this->_setAuthors($xml);
        $this->_setEnclosure($xml);
        $this->_setContent($xml);
        $this->_setCategories($xml);

        $xml->endElement(); // entry
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
        if (!$this->entry->getTitle()) {
            throw new InvalidArgumentException(
                'Atom 1.0 entry elements MUST contain exactly one'
                . ' atom:title element but a title has not been set'
            );
        }

        $xml->startElement('title');
        $xml->writeAttribute('type', 'html');
        $xml->writeCData($this->entry->getTitle());
        $xml->endElement(); // title
    }

    /**
     * Set entry description
     *
     * @param  XMLWriter $xml
     * @return void
     */
    protected function _setDescription(XMLWriter $xml)
    {
        if (!$this->entry->getDescription()) {
            return; // unless src content or base64
        }

        $xml->startElement('summary');
        $xml->writeAttribute('type', 'html');
        $xml->writeCData($this->entry->getDescription());
        $xml->endElement(); // summary
    }

    /**
     * Set date entry was modified
     *
     * @param  XMLWriter $xml
     * @return void
     * @throws InvalidArgumentException
     */
    protected function _setDateModified(XMLWriter $xml)
    {
        if (!$this->entry->getDateModified()) {
            throw new InvalidArgumentException(
                'Atom 1.0 entry elements MUST contain exactly one'
                . ' atom:updated element but a modification date has not been set'
            );
        }

        $xml->writeElement(
            'updated', 
            $this->entry->getDateModified()->format(DateTime::ISO8601)
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

        $xml->writeElement(
            'published', 
            $this->entry->getDateCreated()->format(DateTime::ISO8601)
        );
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
            /**
             * This will actually trigger an Exception at the feed level if
             * a feed level author is not set.
             */
            return;
        }

        foreach ($authors as $data) {

            $xml->startElement('author');

            $xml->writeElement('name', $data['name']);

            if (array_key_exists('email', $data)) {
                $xml->writeElement('email', $data['email']);
            }

            if (array_key_exists('uri', $data)) {
                $xml->writeElement('uri', $data['uri']);
            }

            $xml->endElement(); // author
        }
    }

    /**
     * Set entry enclosure
     *
     * @param  XMLWriter $xml
     * @return void
     */
    protected function _setEnclosure(XMLWriter $xml)
    {
        $data = $this->entry->getEnclosure();

        if ((!$data || empty($data))) {
            return;
        }

        $xml->startElement('link');

        $xml->writeAttribute('rel', 'enclosure');

        if (isset($data['type'])) {
            $xml->writeAttribute('type', $data['type']);
        }

        if (isset($data['length'])) {
            $xml->writeAttribute('length', $data['length']);
        }

        $xml->writeAttribute('href', $data['uri']);

        $xml->endElement(); // link
    }

    protected function _setLink(XMLWriter $xml)
    {
        if (!$this->entry->getLink()) {
            return;
        }

        $xml->startElement('link');
        $xml->writeAttribute('rel', 'alternate');
        $xml->writeAttribute('type', 'text/html');
        $xml->writeAttribute('href', $this->entry->getLink());
        $xml->endElement(); // link
    }

    /**
     * Set entry identifier
     *
     * @param  XMLWriter $xml
     * @return void
     * @throws InvalidArgumentException
     */
    protected function _setId(XMLWriter $xml)
    {
        if (!$this->entry->getId()
            && !$this->entry->getLink()
        ) {
            throw new InvalidArgumentException(
                'Atom 1.0 entry elements MUST contain exactly one '
                . 'atom:id element, or as an alternative, we can use the same '
                . 'value as atom:link however neither a suitable link nor an '
                . 'id have been set'
            );
        }

        if (!$this->entry->getId()) {
            $this->entry->setId(
                $this->entry->getLink()
            );
        }

        if (!Uri::factory($this->entry->getId())->isValid()
            && !preg_match(
                "#^urn:[a-zA-Z0-9][a-zA-Z0-9\-]{1,31}:([a-zA-Z0-9\(\)\+\,\.\:\=\@\;\$\_\!\*\-]|%[0-9a-fA-F]{2})*#",
                $this->entry->getId()
            )
            && !$this->_validateTagUri($this->entry->getId())
        ) {
            throw new InvalidArgumentException('Atom 1.0 IDs must be a valid URI/IRI');
        }

        $xml->writeElement('id', $this->entry->getId());
    }

    /**
     * Validate a URI using the tag scheme (RFC 4151)
     *
     * @param string $id
     * @return bool
     */
    protected function _validateTagUri($id)
    {
        if (preg_match('/^tag:(?P<name>.*),(?P<date>\d{4}-?\d{0,2}-?\d{0,2}):(?P<specific>.*)(.*:)*$/', $id, $matches)) {
            $dvalid = false;
            $nvalid = false;
            $date = $matches['date'];
            $d6 = strtotime($date);

            if ((strlen($date) == 4) && $date <= date('Y')) {
                $dvalid = true;
            } elseif ((strlen($date) == 7) && ($d6 < strtotime("now"))) {
                $dvalid = true;
            } elseif ((strlen($date) == 10) && ($d6 < strtotime("now"))) {
                $dvalid = true;
            }

            if (filter_var($matches['name'], FILTER_VALIDATE_EMAIL)) {
                $nvalid = true;
            } else {
                $nvalid = filter_var('info@' . $matches['name'], FILTER_VALIDATE_EMAIL);
            }

            return $dvalid && $nvalid;

        }
        return false;
    }

    /**
     * Set entry content
     *
     * @param  XMLWriter $xml
     * @return void
     * @throws InvalidArgumentException
     */
    protected function _setContent(XMLWriter $xml)
    {
        $content = $this->entry->getContent();

        if (!$content && !$this->entry->getLink()) {
            throw new InvalidArgumentException(
                'Atom 1.0 entry elements MUST contain exactly one '
                . 'atom:content element, or as an alternative, at least one link '
                . 'with a rel attribute of "alternate" to indicate an alternate '
                . 'method to consume the content.'
            );
        }

        if (!$content) {
            return;
        }

        $xml->startElement('content');
        $xml->writeAttribute('type', 'xhtml');
        $xml->writeRaw(rtrim($this->_loadXhtml($content)));
        $xml->endElement(); // content
    }

    /**
     * Load a HTML string and attempt to normalise to XML
     */
    protected function _loadXhtml($content)
    {
        $xhtml = '';

        if (class_exists('tidy', false)) {
            $tidy = new \tidy;
            $config = array(
                'output-xhtml' => true,
                'show-body-only' => true,
                'quote-nbsp' => false
            );
            $encoding = str_replace('-', '', $this->getEncoding());
            $tidy->parseString($content, $config, $encoding);
            $tidy->cleanRepair();
            $xhtml = (string) $tidy;
        } else {
            $xhtml = $content;
        }
        $xhtml = preg_replace(array(
            "/(<[\/]?)([a-zA-Z]+)/"
        ), '$1xhtml:$2', $xhtml);
        $dom = new DOMDocument('1.0', $this->getEncoding());
        $dom->loadXML(
            '<xhtml:div xmlns:xhtml="http://www.w3.org/1999/xhtml">' 
            . $xhtml 
            . '</xhtml:div>'
        );
        return $dom->saveHTML();
    }

    /**
     * Set entry categories
     *
     * @param  XMLWriter $xml
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

            $xml->writeAttribute('term', $cat['term']);

            if (isset($cat['label'])) {
                $xml->writeAttribute('label', $cat['label']);
            } else {
                $xml->writeAttribute('label', $cat['term']);
            }

            if (isset($cat['scheme'])) {
                $xml->writeAttribute('scheme', $cat['scheme']);
            }

            $xml->endElement(); // category
        }
    }
}
