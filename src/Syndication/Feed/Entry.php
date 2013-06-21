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
namespace Syndication\Feed;

use DateTime;
use InvalidArgumentException;
use Syndication\Uri;

class Entry
{
    /**
     * Internal array containing all data associated with this entry or item.
     *
     * @var array
     */
    protected $data = array();

    /**
     * Set the feed character encoding
     *
     * @param string $encoding
     * @throws InvalidArgumentException
     * @return Syndication\Feed\Entry
     */
    public function setEncoding($encoding)
    {
        if (empty($encoding) || !is_string($encoding)) {
            throw new InvalidArgumentException(
                'Invalid parameter: parameter must be a non-empty string'
            );
        }

        $this->data['encoding'] = $encoding;

        return $this;
    }

    /**
     * Set a single author
     *
     * The following option keys are supported:
     * 'name'  => (string) The name
     * 'email' => (string) An optional email
     * 'uri'   => (string) An optional and valid URI
     *
     * @param array $author
     * @throws InvalidArgumentException If any value of $author not follow the format.
     * @return Syndication\Feed\Entry
     */
    public function addAuthor(array $author)
    {
        // Check array values
        if (!array_key_exists('name', $author)
            || empty($author['name'])
            || !is_string($author['name'])
        ) {
            throw new InvalidArgumentException(
                'Invalid parameter: author array must include a "name" key with a non-empty string value'
            );
        }

        if (isset($author['email'])) {
            if (empty($author['email']) || !is_string($author['email'])) {
                throw new InvalidArgumentException(
                    'Invalid parameter: "email" array value must be a non-empty string'
                );
            }
        }
        if (isset($author['uri'])) {
            if (empty($author['uri']) || !is_string($author['uri']) ||
                !Uri::factory($author['uri'])->isValid()
            ) {
                throw new InvalidArgumentException(
                    'Invalid parameter: "uri" array value must be a non-empty string and valid URI/IRI'
                );
            }
        }

        $this->data['authors'][] = $author;

        return $this;
    }

    /**
     * Set an array with feed authors
     *
     * @see addAuthor
     * @param array $authors
     * @return Syndication\Feed\Entry
     */
    public function addAuthors(array $authors)
    {
        foreach ($authors as $author) {
            $this->addAuthor($author);
        }

        return $this;
    }

    /**
     * Set the copyright entry
     *
     * @param string $copyright
     * @throws InvalidArgumentException
     * @return Syndication\Feed\Entry
     */
    public function setCopyright($copyright)
    {
        if (empty($copyright) || !is_string($copyright)) {
            throw new InvalidArgumentException(
                'Invalid parameter: parameter must be a non-empty string'
            );
        }

        $this->data['copyright'] = $copyright;

        return $this;
    }

    /**
     * Set the entry's content
     *
     * @param string $content
     * @throws InvalidArgumentException
     * @return Syndication\Feed\Entry
     */
    public function setContent($content)
    {
        if (empty($content) || !is_string($content)) {
            throw new InvalidArgumentException(
                'Invalid parameter: parameter must be a non-empty string'
            );
        }

        $this->data['content'] = $content;

        return $this;
    }

    /**
     * Set the feed creation date
     *
     * @param null|int|DateTime $date
     * @throws InvalidArgumentException
     * @return Syndication\Feed\Entry
     */
    public function setDateCreated($date = null)
    {
        if ($date === null) {
            $date = new DateTime();
        } elseif (is_int($date)) {
            $date = new DateTime('@' . $date);
        } elseif (!$date instanceof DateTime) {
            throw new InvalidArgumentException(
                'Invalid DateTime object or UNIX Timestamp passed as parameter'
            );
        }

        $this->data['dateCreated'] = $date;

        return $this;
    }

    /**
     * Set the feed modification date
     *
     * @param null|int|DateTime $date
     * @throws InvalidArgumentException
     * @return Syndication\Feed\Entry
     */
    public function setDateModified($date = null)
    {
        if ($date === null) {
            $date = new DateTime();
        } elseif (is_int($date)) {
            $date = new DateTime('@' . $date);
        } elseif (!$date instanceof DateTime) {
            throw new InvalidArgumentException(
                'Invalid DateTime object or UNIX Timestamp passed as parameter'
            );
        }

        $this->data['dateModified'] = $date;

        return $this;
    }

    /**
     * Set the feed description
     *
     * @param string $description
     * @throws InvalidArgumentException
     * @return Syndication\Feed\Entry
     */
    public function setDescription($description)
    {
        if (empty($description) || !is_string($description)) {
            throw new InvalidArgumentException(
                'Invalid parameter: parameter must be a non-empty string'
            );
        }

        $this->data['description'] = $description;

        return $this;
    }

    /**
     * Set the feed ID
     *
     * @param string $id
     * @throws InvalidArgumentException
     * @return Syndication\Feed\Entry
     */
    public function setId($id)
    {
        if (empty($id) || !is_string($id)) {
            throw new InvalidArgumentException(
                'Invalid parameter: parameter must be a non-empty string'
            );
        }

        $this->data['id'] = $id;

        return $this;
    }

    /**
     * Set a link to the HTML source of this entry
     *
     * @param string $link
     * @throws InvalidArgumentException
     * @return Syndication\Feed\Entry
     */
    public function setLink($link)
    {
        if (empty($link) || !is_string($link) || !Uri::factory($link)->isValid()) {
            throw new InvalidArgumentException(
                'Invalid parameter: parameter must be a non-empty string and valid URI/IRI'
            );
        }

        $this->data['link'] = $link;

        return $this;
    }

    /**
     * Set the number of comments associated with this entry
     *
     * @param int $count
     * @throws InvalidArgumentException
     * @return Syndication\Feed\Entry
     */
    public function setCommentCount($count)
    {
        if (!is_numeric($count) || (int) $count != $count || (int) $count < 0) {
            throw new InvalidArgumentException(
                'Invalid parameter: "count" must be a positive integer number or zero'
            );
        }

        $this->data['commentCount'] = (int) $count;

        return $this;
    }

    /**
     * Set a link to a HTML page containing comments associated with this entry
     *
     * @param string $link
     * @throws InvalidArgumentException
     * @return Syndication\Feed\Entry
     */
    public function setCommentLink($link)
    {
        if (empty($link) || !is_string($link) || !Uri::factory($link)->isValid()) {
            throw new InvalidArgumentException(
                'Invalid parameter: "link" must be a non-empty string and valid URI/IRI'
            );
        }

        $this->data['commentLink'] = $link;

        return $this;
    }

    /**
     * Set a link to an XML feed for any comments associated with this entry
     *
     * @param array $link
     * @throws InvalidArgumentException
     * @return Syndication\Feed\Entry
     */
    public function setCommentFeedLink(array $link)
    {
        if (!isset($link['uri']) || !is_string($link['uri']) || !Uri::factory($link['uri'])->isValid()) {
            throw new InvalidArgumentException(
                'Invalid parameter: "link" must be a non-empty string and valid URI/IRI'
            );
        }

        if (!isset($link['type']) || !in_array($link['type'], array('atom', 'rss', 'rdf'))) {
            throw new InvalidArgumentException(
                'Invalid parameter: "type" must be one of "atom", "rss" or "rdf"'
            );
        }

        if (!isset($this->data['commentFeedLinks'])) {
            $this->data['commentFeedLinks'] = array();
        }

        $this->data['commentFeedLinks'][] = $link;

        return $this;
    }

    /**
     * Set a links to an XML feed for any comments associated with this entry.
     * Each link is an array with keys "uri" and "type", where type is one of:
     * "atom", "rss" or "rdf".
     *
     * @param array $links
     * @return Syndication\Feed\Entry
     */
    public function setCommentFeedLinks(array $links)
    {
        foreach ($links as $link) {
            $this->setCommentFeedLink($link);
        }

        return $this;
    }

    /**
     * Set the feed title
     *
     * @param string $title
     * @throws InvalidArgumentException
     * @return Syndication\Feed\Entry
     */
    public function setTitle($title)
    {
        if (empty($title) || !is_string($title)) {
            throw new InvalidArgumentException(
                'Invalid parameter: parameter must be a non-empty string'
            );
        }

        $this->data['title'] = $title;

        return $this;
    }

    /**
     * Get the feed character encoding
     *
     * @return string|null
     */
    public function getEncoding()
    {
        if (!array_key_exists('encoding', $this->data)) {
            return 'UTF-8';
        }

        return $this->data['encoding'];
    }

    /**
     * Get an array with feed authors
     *
     * @return array
     */
    public function getAuthors()
    {
        if (!array_key_exists('authors', $this->data)) {
            return null;
        }

        return $this->data['authors'];
    }

    /**
     * Get the entry content
     *
     * @return string
     */
    public function getContent()
    {
        if (!array_key_exists('content', $this->data)) {
            return null;
        }

        return $this->data['content'];
    }

    /**
     * Get the entry copyright information
     *
     * @return string
     */
    public function getCopyright()
    {
        if (!array_key_exists('copyright', $this->data)) {
            return null;
        }

        return $this->data['copyright'];
    }

    /**
     * Get the entry creation date
     *
     * @return string
     */
    public function getDateCreated()
    {
        if (!array_key_exists('dateCreated', $this->data)) {
            return null;
        }

        return $this->data['dateCreated'];
    }

    /**
     * Get the entry modification date
     *
     * @return string
     */
    public function getDateModified()
    {
        if (!array_key_exists('dateModified', $this->data)) {
            return null;
        }

        return $this->data['dateModified'];
    }

    /**
     * Get the entry description
     *
     * @return string
     */
    public function getDescription()
    {
        if (!array_key_exists('description', $this->data)) {
            return null;
        }

        return $this->data['description'];
    }

    /**
     * Get the entry ID
     *
     * @return string
     */
    public function getId()
    {
        if (!array_key_exists('id', $this->data)) {
            return null;
        }

        return $this->data['id'];
    }

    /**
     * Get a link to the HTML source
     *
     * @return string|null
     */
    public function getLink()
    {
        if (!array_key_exists('link', $this->data)) {
            return null;
        }

        return $this->data['link'];
    }


    /**
     * Get all links
     *
     * @return array
     */
    public function getLinks()
    {
        if (!array_key_exists('links', $this->data)) {
            return null;
        }

        return $this->data['links'];
    }

    /**
     * Get the entry title
     *
     * @return string
     */
    public function getTitle()
    {
        if (!array_key_exists('title', $this->data)) {
            return null;
        }

        return $this->data['title'];
    }

    /**
     * Get the number of comments/replies for current entry
     *
     * @return int
     */
    public function getCommentCount()
    {
        if (!array_key_exists('commentCount', $this->data)) {
            return null;
        }

        return $this->data['commentCount'];
    }

    /**
     * Returns a URI pointing to the HTML page where comments can be made on this entry
     *
     * @return string
     */
    public function getCommentLink()
    {
        if (!array_key_exists('commentLink', $this->data)) {
            return null;
        }

        return $this->data['commentLink'];
    }

    /**
     * Returns an array of URIs pointing to a feed of all comments for this entry
     * where the array keys indicate the feed type (atom, rss or rdf).
     *
     * @return string
     */
    public function getCommentFeedLinks()
    {
        if (!array_key_exists('commentFeedLinks', $this->data)) {
            return null;
        }

        return $this->data['commentFeedLinks'];
    }

    /**
     * Add a entry category
     *
     * @param array $category
     * @throws InvalidArgumentException
     * @return Entry
     */
    public function addCategory(array $category)
    {
        if (!isset($category['term'])) {
            throw new InvalidArgumentException(
                'Each category must be an array and '
                . 'contain at least a "term" element containing the machine '
                . ' readable category name'
            );
        }

        if (isset($category['scheme'])) {
            if (empty($category['scheme'])
                || !is_string($category['scheme'])
                || !Uri::factory($category['scheme'])->isValid()
            ) {
                throw new InvalidArgumentException(
                    'The Atom scheme or RSS domain of'
                    . ' a category must be a valid URI'
                );
            }
        }

        if (!isset($this->data['categories'])) {
            $this->data['categories'] = array();
        }

        $this->data['categories'][] = $category;

        return $this;
    }

    /**
     * Set an array of entry categories
     *
     * @param array $categories
     * @return Entry
     */
    public function addCategories(array $categories)
    {
        foreach ($categories as $category) {
            $this->addCategory($category);
        }

        return $this;
    }

    /**
     * Get the entry categories
     *
     * @return string|null
     */
    public function getCategories()
    {
        if (!array_key_exists('categories', $this->data)) {
            return null;
        }

        return $this->data['categories'];
    }

    /**
     * Adds an enclosure to the entry. The array parameter may contain the
     * keys 'uri', 'type' and 'length'. Only 'uri' is required for Atom, though the
     * others must also be provided or RSS rendering (where they are required)
     * will throw an Exception.
     *
     * @param array $enclosure
     * @throws InvalidArgumentException
     * @return Entry
     */
    public function setEnclosure(array $enclosure)
    {
        if (!isset($enclosure['uri'])) {
            throw new InvalidArgumentException(
                'Enclosure "uri" is not set'
            );
        }

        if (!Uri::factory($enclosure['uri'])->isValid()) {
            throw new InvalidArgumentException(
                'Enclosure "uri" is not a valid URI/IRI'
            );
        }

        $this->data['enclosure'] = $enclosure;

        return $this;
    }

    /**
     * Retrieve an array of all enclosures to be added to entry.
     *
     * @return array
     */
    public function getEnclosure()
    {
        if (!array_key_exists('enclosure', $this->data)) {
            return null;
        }

        return $this->data['enclosure'];
    }

    /**
     * Unset a specific data point
     *
     * @param string $name
     * @return Syndication\Feed\Entry
     */
    public function remove($name)
    {
        if (isset($this->data[$name])) {
            unset($this->data[$name]);
        }

        return $this;
    }
}