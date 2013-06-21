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

use XMLWriter;
use InvalidArgumentException;
use DateTime;
use Syndication\Uri;
use Syndication\Version;

class Atom extends AbstractWriter implements WriterInterface
{
    /**
     * Render Atom feed
     * 
     * @return string
     */
    public function render()
    {
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->setIndentString('  ');
        $xml->startDocument('1.0', $this->feed->getEncoding());
        $xml->startElement('feed');
        $xml->writeAttribute('xmlns', 'http://www.w3.org/2005/Atom');

        $this->_setLanguage($xml);
        $this->_setBaseUrl($xml);
        $this->_setTitle($xml);
        $this->_setDescription($xml);
        $this->_setImage($xml);
        $this->_setDateCreated($xml);
        $this->_setDateModified($xml);
        $this->_setGenerator($xml);
        $this->_setLink($xml);
        $this->_setFeedLinks($xml);
        $this->_setId($xml);
        $this->_setAuthors($xml);
        $this->_setCopyright($xml);
        $this->_setCategories($xml);
        $this->_setHubs($xml);

        
        foreach ($this->feed as $entry) {
            (new Entry\Atom($entry))->render($xml);
        }

        $xml->endElement(); // feed
        $xml->endDocument();

        return $xml->flush();
    }

    /**
     * Set feed language
     *
     * @param  XMLWriter $xml
     * @return void
     */
    protected function _setLanguage(XMLWriter $xml)
    {
        if ($this->feed->getLanguage()) {
            $xml->writeAttributeNS(
                'xml', 
                'lang', 
                null, 
                $this->feed->getLanguage()
            );
        }
    }

    /**
     * Set feed title
     *
     * @param  XMLWriter $xml
     * @return void
     * @throws InvalidArgumentException
     */
    protected function _setTitle(XMLWriter $xml)
    {
        if (!$this->feed->getTitle()) {
            throw new InvalidArgumentException(
                'Atom 1.0 feed elements MUST contain exactly one'
                . ' atom:title element but a title has not been set'
            );
        }

        $xml->startElement('title');
        $xml->writeAttribute('type', 'text');
        $xml->text($this->feed->getTitle());
        $xml->endElement(); // title
    }

    /**
     * Set feed description
     *
     * @param  XMLWriter $xml
     * @return void
     */
    protected function _setDescription(XMLWriter $xml)
    {
        if (!$this->feed->getDescription()) {
            return;
        }

        $xml->startElement('subtitle');
        $xml->writeAttribute('type', 'text');
        $xml->text($this->feed->getDescription());
        $xml->endElement(); // subtitle
    }

    /**
     * Set date feed was last modified
     *
     * @param  XMLWriter $xml
     * @return void
     * @throws InvalidArgumentException
     */
    protected function _setDateModified(XMLWriter $xml)
    {
        if (!$this->feed->getDateModified()) {
            throw new InvalidArgumentException(
                'Atom 1.0 feed elements MUST contain exactly one'
                . ' atom:updated element but a modification date has not been set'
            );
        }

        $xml->writeElement(
            'updated', 
            $this->feed->getDateModified()->format(DateTime::RFC3339)
        );
    }

    /**
     * Set feed generator string
     *
     * @param  XMLWriter $xml
     * @return void
     */
    protected function _setGenerator(XMLWriter $xml)
    {
        if (!$this->feed->getGenerator()) {
            $this->feed->setGenerator(
                Version::NAME,
                Version::VERSION, 
                'https://github.com/euskadi31/Syndication'
            );
        }

        $gdata = $this->feed->getGenerator();

        $xml->startElement('generator');

        if (array_key_exists('uri', $gdata)) {
            $xml->writeAttribute('uri', $gdata['uri']);
        }
        if (array_key_exists('version', $gdata)) {
            $xml->writeAttribute('version', $gdata['version']);
        }

        $xml->text($gdata['name']);

        $xml->endElement(); // generator
    }

    /**
     * Set link to feed
     *
     * @param  XMLWriter $xml
     * @return void
     */
    protected function _setLink(XMLWriter $xml)
    {
        if (!$this->feed->getLink()) {
            return;
        }

        $xml->startElement('link');
        $xml->writeAttribute('rel', 'alternate');
        $xml->writeAttribute('type', 'text/html');
        $xml->writeAttribute('href', $this->feed->getLink());
        $xml->endElement(); // link
    }

    /**
     * Set feed links
     *
     * @param  XMLWriter $xml
     * @return void
     * @throws InvalidArgumentException
     */
    protected function _setFeedLinks(XMLWriter $xml)
    {
        $flinks = $this->feed->getFeedLinks();

        if (!$flinks || !array_key_exists('atom', $flinks)) {

            throw new InvalidArgumentException(
                'Atom 1.0 feed elements SHOULD contain one atom:link '
                . 'element with a rel attribute value of "self".  This is the '
                . 'preferred URI for retrieving Atom Feed Documents representing '
                . 'this Atom feed but a feed link has not been set'
            );
        }

        foreach ($flinks as $type => $href) {
            $mime = 'application/' . strtolower($type) . '+xml';

            $xml->startElement('link');
            $xml->writeAttribute('rel', 'self');
            $xml->writeAttribute('type', $mime);
            $xml->writeAttribute('href', $href);
            $xml->endElement(); // link
        }
    }

    /**
     * Set feed authors
     *
     * @param  XMLWriter $xml
     * @return void
     */
    protected function _setAuthors(XMLWriter $xml)
    {
        $authors = $this->feed->getAuthors();

        if (!$authors || empty($authors)) {
            /**
             * Technically we should defer an exception until we can check
             * that all entries contain an author. If any entry is missing
             * an author, then a missing feed author element is invalid
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
     * Set feed identifier
     *
     * @param  XMLWriter $xml
     * @return void
     * @throws InvalidArgumentException
     */
    protected function _setId(XMLWriter $xml)
    {
        if (!$this->feed->getId()
            && !$this->feed->getLink()
        ) {
            throw new InvalidArgumentException(
                'Atom 1.0 feed elements MUST contain exactly one '
                . 'atom:id element, or as an alternative, we can use the same '
                . 'value as atom:link however neither a suitable link nor an '
                . 'id have been set'
            );
        }

        if (!$this->feed->getId()) {
            $this->feed->setId(
                $this->feed->getLink()
            );
        }

        $xml->writeElement('id', $this->feed->getId());
    }

    /**
     * Set feed copyright
     *
     * @param  XMLWriter $xml
     * @return void
     */
    protected function _setCopyright(XMLWriter $xml)
    {
        $copyright = $this->feed->getCopyright();

        if (!$copyright) {
            return;
        }

        $xml->writeElement('rights', $copyright);
    }

    /**
     * Set feed level logo (image)
     *
     * @param XMLWriter $xml
     * @return void
     */
    protected function _setImage(XMLWriter $xml)
    {
        $image = $this->feed->getImage();

        if (!$image) {
            return;
        }

        $xml->writeElement('logo', $image['uri']);
    }

    /**
     * Set date feed was created
     *
     * @param  XMLWriter $xml
     * @return void
     */
    protected function _setDateCreated(XMLWriter $xml)
    {
        if (!$this->feed->getDateCreated()) {
            return;
        }

        if (!$this->feed->getDateModified()) {
            $this->feed->setDateModified(
                $this->feed->getDateCreated()
            );
        }
    }

    /**
     * Set base URL to feed links
     *
     * @param  XMLWriter $xml
     * @return void
     */
    protected function _setBaseUrl(XMLWriter $xml)
    {
        $baseUrl = $this->feed->getBaseUrl();

        if (!$baseUrl) {
            return;
        }

        $xml->writeAttributeNS('xml', 'base', null, $baseUrl);
    }

    /**
     * Set hubs to which this feed pushes
     *
     * @param  XMLWriter $xml
     * @return void
     */
    protected function _setHubs(XMLWriter $xml)
    {
        $hubs = $this->feed->getHubs();

        if (!$hubs) {
            return;
        }

        foreach ($hubs as $hubUrl) {
            $xml->startElement('link');
            $xml->writeAttribute('rel', 'hub');
            $xml->writeAttribute('href', $hubUrl);
            $xml->endElement(); // link
        }
    }

    /**
     * Set feed categories
     *
     * @param  XMLWriter $xml
     * @return void
     */
    protected function _setCategories(XMLWriter $xml)
    {
        $categories = $this->feed->getCategories();

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