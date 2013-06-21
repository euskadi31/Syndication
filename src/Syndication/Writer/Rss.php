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

class Rss extends AbstractWriter implements WriterInterface
{
    /**
     * Render RSS feed
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
        $xml->startElement('rss');
        $xml->writeAttribute('version', '2.0');
        $xml->writeAttributeNS('xmlns', 'atom', null, 'http://www.w3.org/2005/Atom');
        $xml->startElement('channel');
        
        $this->_setBaseUrl($xml);

        $this->_setTitle($xml);
        $this->_setDescription($xml);
        $this->_setLanguage($xml);
        $this->_setImage($xml);
        $this->_setDateCreated($xml);
        $this->_setDateModified($xml);
        $this->_setLastBuildDate($xml);
        $this->_setGenerator($xml);
        
        $xml->writeElement('docs', 'http://www.rssboard.org/rss-specification');

        $this->_setLink($xml);
        $this->_setFeedLinks($xml);
        $this->_setCopyright($xml);
        $this->_setCategories($xml);
        
        foreach ($this->feed as $entry) {
            (new Entry\Rss($entry))->render($xml);
        }

        $xml->endElement(); // channel
        $xml->endElement(); // rss
        $xml->endDocument();

        return $xml->flush();
    }

    /**
     * Set feed language
     *
     * @param XMLWriter $xml
     * @return void
     */
    protected function _setLanguage(XMLWriter $xml)
    {
        $lang = $this->feed->getLanguage();

        if (!$lang) {
            return;
        }

        $xml->writeElement('language', $lang);
    }

    /**
     * Set feed title
     *
     * @param XMLWriter $xml
     * @return void
     * @throws InvalidArgumentException
     */
    protected function _setTitle(XMLWriter $xml)
    {
        if (!$this->feed->getTitle()) {

            throw new InvalidArgumentException(
                'RSS 2.0 feed elements MUST contain exactly one title element but a title has not been set'
            );
        }

        $xml->writeElement('title', $this->feed->getTitle());
    }

    /**
     * Set feed description
     *
     * @param  XMLWriter $xml
     * @return void
     * @throws InvalidArgumentException
     */
    protected function _setDescription(XMLWriter $xml)
    {
        if (!$this->feed->getDescription()) {
            throw new InvalidArgumentException(
                'RSS 2.0 feed elements MUST contain exactly one description element but one has not been set'
            );
        }

        $xml->writeElement('description', $this->feed->getDescription());
    }

    /**
     * Set date feed was last modified
     *
     * @param  XMLWriter $xml
     * @return void
     */
    protected function _setDateModified(XMLWriter $xml)
    {
        if (!$this->feed->getDateModified()) {
            return;
        }

        $xml->writeElement(
            'pubDate', 
            $this->feed->getDateModified()->format(DateTime::RSS)
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

        $name = $gdata['name'];

        if (array_key_exists('version', $gdata)) {
            $name .= ' ' . $gdata['version'];
        }

        if (array_key_exists('uri', $gdata)) {
            $name .= ' (' . $gdata['uri'] . ')';
        }

        $xml->writeElement(
            'generator', 
            $name
        );
    }

    /**
     * Set link to feed
     *
     * @param  XMLWriter $xml
     * @return void
     * @throws InvalidArgumentException
     */
    protected function _setLink(XMLWriter $xml)
    {
        $value = $this->feed->getLink();

        if (!$value) {
            throw new InvalidArgumentException(
                'RSS 2.0 feed elements MUST contain exactly one link element but one has not been set'
            );
        }

        $xml->startElement('link');

        if (!Uri::factory($value)->isValid()) {
            $xml->writeAttribute('isPermaLink', 'false');
        }

        $xml->text($value);
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

        if (!$flinks || !array_key_exists('rss', $flinks)) {

            throw new InvalidArgumentException(
                'Rss 2.0 feed elements SHOULD contain one atom:link '
                . 'element with a rel attribute value of "self".  This is the '
                . 'preferred URI for retrieving Rss Feed Documents representing '
                . 'this Rss feed but a feed link has not been set'
            );
        }

        foreach ($flinks as $type => $href) {
            $mime = 'application/' . strtolower($type) . '+xml';

            $xml->startElementNS('atom', 'link', null);
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

        $xml->writeElement('copyright', $copyright);
    }

    /**
     * Set feed channel image
     *
     * @param  XMLWriter $xml
     * @return void
     * @throws InvalidArgumentException
     */
    protected function _setImage(XMLWriter $xml)
    {
        $image = $this->feed->getImage();

        if (!$image) {
            return;
        }

        if (!isset($image['title']) || empty($image['title'])
            || !is_string($image['title'])
        ) {
            throw new InvalidArgumentException(
                'RSS 2.0 feed images must include a title'
            );
        }

        if (empty($image['link']) || !is_string($image['link'])
            || !Uri::factory($image['link'])->isValid()
        ) {
            throw new InvalidArgumentException(
                'Invalid parameter: parameter \'link\' must be a non-empty string and valid URI/IRI'
            );
        }

        $xml->startElement('image');

        $xml->writeElement('url', $image['uri']);
        $xml->writeElement('title', $image['title']);
        $xml->writeElement('link', $image['link']);

        if (isset($image['height'])) {
            if (!ctype_digit((string) $image['height']) || $image['height'] > 400) {
                throw new InvalidArgumentException(
                    'Invalid parameter: parameter \'height\' must be an integer not exceeding 400'
                );
            }

            $xml->writeElement('height', $image['height']);
        }

        if (isset($image['width'])) {
            if (!ctype_digit((string) $image['width']) || $image['width'] > 144) {
                throw new InvalidArgumentException(
                    'Invalid parameter: parameter \'width\' must be an integer not exceeding 144'
                );
            }

            $xml->writeElement('width', $image['width']);
        }

        if (isset($image['description'])) {
            if (empty($image['description']) || !is_string($image['description'])) {
                throw new InvalidArgumentException(
                    'Invalid parameter: parameter \'description\' must be a non-empty string'
                );
            }

            $xml->writeElement('description', $image['description']);
        }

        $xml->endElement(); // image
        
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
     * Set date feed last build date
     *
     * @param  XMLWriter $xml
     * @return void
     */
    protected function _setLastBuildDate(XMLWriter $xml)
    {
        if (!$this->feed->getLastBuildDate()) {
            return;
        }

        $xml->writeElement(
            'lastBuildDate', 
            $this->feed->getLastBuildDate()->format(DateTime::RSS)
        );
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
            
            if (isset($cat['scheme'])) {
                $xml->writeAttribute('domain', $cat['scheme']);
            }

            $xml->writeCData($cat['term']);

            $xml->endElement(); // category
        }
    }

}