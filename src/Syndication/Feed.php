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
namespace Syndication;

use Iterator;
use Countable;
use Syndication\Feed\Entry;
use DateTime;
use Locale;

class Feed implements Iterator, Countable
{
    /**
     * Contains all Feed level date to append in feed output
     *
     * @var array
     */
    protected $data = array();

    /**
     * Holds the value "atom" or "rss" depending on the feed type set when
     * when last exported.
     *
     * @var string
     */
    protected $type = null;

    /**
     * Contains all entry objects
     *
     * @var array
     */
    protected $entries = array();

    /**
     * A pointer for the iterator to keep track of the entries array
     *
     * @var int
     */
    protected $entriesKey = 0;

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
     * @return Syndication\Feed
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
     * @return Syndication\Feed
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
     * @param  string      $copyright
     * @throws InvalidArgumentException
     * @return Syndication\Feed
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
     * Set the feed creation date
     *
     * @param null|int|DateTime
     * @throws InvalidArgumentException
     * @return Syndication\Feed
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
     * @param null|int|DateTime
     * @throws InvalidArgumentException
     * @return Syndication\Feed
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
     * Set the feed last-build date. Ignored for Atom 1.0.
     *
     * @param null|int|DateTime
     * @throws InvalidArgumentException
     * @return Syndication\Feed
     */
    public function setLastBuildDate($date = null)
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

        $this->data['lastBuildDate'] = $date;

        return $this;
    }

    /**
     * Set the feed description
     *
     * @param string $description
     * @throws InvalidArgumentException
     * @return Syndication\Feed
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
     * Set the feed generator entry
     *
     * @param array|string $name
     * @param null|string $version
     * @param null|string $uri
     * @throws InvalidArgumentException
     * @return Syndication\Feed
     */
    public function setGenerator($name, $version = null, $uri = null)
    {
        if (is_array($name)) {
            $data = $name;
            if (empty($data['name']) || !is_string($data['name'])) {
                throw new InvalidArgumentException(
                    'Invalid parameter: "name" must be a non-empty string'
                );
            }
            $generator = array('name' => $data['name']);
            if (isset($data['version'])) {
                if (empty($data['version']) || !is_string($data['version'])) {
                    throw new InvalidArgumentException(
                        'Invalid parameter: "version" must be a non-empty string'
                    );
                }
                $generator['version'] = $data['version'];
            }
            if (isset($data['uri'])) {
                if (empty($data['uri']) || !is_string($data['uri']) || !Uri::factory($data['uri'])->isValid()) {
                    throw new InvalidArgumentException(
                        'Invalid parameter: "uri" must be a non-empty string and a valid URI/IRI'
                    );
                }
                $generator['uri'] = $data['uri'];
            }
        } else {
            if (empty($name) || !is_string($name)) {
                throw new InvalidArgumentException(
                    'Invalid parameter: "name" must be a non-empty string'
                );
            }
            $generator = array('name' => $name);
            if (isset($version)) {
                if (empty($version) || !is_string($version)) {
                    throw new InvalidArgumentException(
                        'Invalid parameter: "version" must be a non-empty string'
                    );
                }
                $generator['version'] = $version;
            }
            if (isset($uri)) {
                if (empty($uri) || !is_string($uri) || !Uri::factory($uri)->isValid()) {
                    throw new InvalidArgumentException(
                        'Invalid parameter: "uri" must be a non-empty string and a valid URI/IRI'
                    );
                }
                $generator['uri'] = $uri;
            }
        }
        $this->data['generator'] = $generator;

        return $this;
    }

    /**
     * Set the feed ID - URI or URN (via PCRE pattern) supported
     *
     * @param string $id
     * @throws InvalidArgumentException
     * @return Syndication\Feed
     */
    public function setId($id)
    {
        if ((empty($id) || !is_string($id) || !Uri::factory($id)->isValid())
            && !preg_match("#^urn:[a-zA-Z0-9][a-zA-Z0-9\-]{1,31}:([a-zA-Z0-9\(\)\+\,\.\:\=\@\;\$\_\!\*\-]|%[0-9a-fA-F]{2})*#", $id)
            && !$this->_validateTagUri($id)
        ) {
            throw new InvalidArgumentException(
                'Invalid parameter: parameter must be a non-empty string and valid URI/IRI'
            );
        }
        $this->data['id'] = $id;

        return $this;
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
     * Set a feed image (URI at minimum). Parameter is a single array with the
     * required key 'uri'. When rendering as RSS, the required keys are 'uri',
     * 'title' and 'link'. RSS also specifies three optional parameters 'width',
     * 'height' and 'description'. Only 'uri' is required and used for Atom rendering.
     *
     * @param array $data
     * @throws InvalidArgumentException
     * @return Syndication\Feed
     */
    public function setImage(array $data)
    {
        if (empty($data['uri']) || !is_string($data['uri'])
            || !Uri::factory($data['uri'])->isValid()
        ) {
            throw new InvalidArgumentException(
                'Invalid parameter: parameter \'uri\' must be a non-empty string and valid URI/IRI'
            );
        }

        $this->data['image'] = $data;

        return $this;
    }

    /**
     * Set the feed language
     *
     * @param string $language
     * @throws InvalidArgumentException
     * @return Syndication\Feed
     */
    public function setLanguage($language)
    {
        if (empty($language) || !is_string($language)) {
            throw new InvalidArgumentException(
                'Invalid parameter: parameter must be a non-empty string'
            );
        }

        if (strlen($language) > 2) {
            $locale = Locale::parseLocale($language);
            if (isset($locale['language'])) {
                $language = $locale['language'];
            } else {
                return $this;
            }
        }
        

        $this->data['language'] = strtolower($language);

        return $this;
    }

    /**
     * Set a link to the HTML source
     *
     * @param string $link
     * @throws InvalidArgumentException
     * @return Syndication\Feed
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
     * Set a link to an XML feed for any feed type/version
     *
     * @param string $link
     * @param string $type
     * @throws InvalidArgumentException
     * @return Syndication\Feed
     */
    public function setFeedLink($link, $type)
    {
        if (empty($link) || !is_string($link) || !Uri::factory($link)->isValid()) {
            throw new InvalidArgumentException(
                'Invalid parameter: "link"" must be a non-empty string and valid URI/IRI'
            );
        }

        if (!in_array(strtolower($type), array('rss', 'rdf', 'atom'))) {
            throw new InvalidArgumentException(
                'Invalid parameter: "type"; You must declare the type of feed the link points to, i.e. RSS, RDF or Atom'
            );
        }

        $this->data['feedLinks'][strtolower($type)] = $link;

        return $this;
    }

    /**
     * Set the feed title
     *
     * @param string $title
     * @throws InvalidArgumentException
     * @return Syndication\Feed
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
     * Set the feed character encoding
     *
     * @param string $encoding
     * @throws InvalidArgumentException
     * @return Syndication\Feed
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
     * Set the feed's base URL
     *
     * @param string $url
     * @throws InvalidArgumentException
     * @return Syndication\Feed
     */
    public function setBaseUrl($url)
    {
        if (empty($url) || !is_string($url) || !Uri::factory($url)->isValid()) {
            throw new InvalidArgumentException(
                'Invalid parameter: "url" array value must be a non-empty string and valid URI/IRI'
            );
        }

        $this->data['baseUrl'] = $url;

        return $this;
    }

    /**
     * Add a Pubsubhubbub hub endpoint URL
     *
     * @param string $url
     * @throws InvalidArgumentException
     * @return Syndication\Feed
     */
    public function addHub($url)
    {
        if (empty($url) || !is_string($url) || !Uri::factory($url)->isValid()) {
            throw new InvalidArgumentException(
                'Invalid parameter: "url" array value must be a non-empty string and valid URI/IRI'
            );
        }

        if (!isset($this->data['hubs'])) {
            $this->data['hubs'] = array();
        }

        $this->data['hubs'][] = $url;

        return $this;
    }

    /**
     * Add Pubsubhubbub hub endpoint URLs
     *
     * @param array $urls
     * @return Syndication\Feed
     */
    public function addHubs(array $urls)
    {
        foreach ($urls as $url) {
            $this->addHub($url);
        }

        return $this;
    }

    /**
     * Add a feed category
     *
     * @param array $category
     * @throws InvalidArgumentException
     * @return Syndication\Feed
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
                    'The Atom scheme or RSS domain of a category must be a valid URI'
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
     * Set an array of feed categories
     *
     * @param array $categories
     * @return Syndication\Feed
     */
    public function addCategories(array $categories)
    {
        foreach ($categories as $category) {
            $this->addCategory($category);
        }

        return $this;
    }

    /**
     * Get a single author
     *
     * @param  int $index
     * @return string|null
     */
    public function getAuthor($index = 0)
    {
        if (isset($this->data['authors'][$index])) {
            return $this->data['authors'][$index];
        }

        return null;
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
     * Get the copyright entry
     *
     * @return string|null
     */
    public function getCopyright()
    {
        if (!array_key_exists('copyright', $this->data)) {
            return null;
        }

        return $this->data['copyright'];
    }

    /**
     * Get the feed creation date
     *
     * @return string|null
     */
    public function getDateCreated()
    {
        if (!array_key_exists('dateCreated', $this->data)) {
            return null;
        }

        return $this->data['dateCreated'];
    }

    /**
     * Get the feed modification date
     *
     * @return string|null
     */
    public function getDateModified()
    {
        if (!array_key_exists('dateModified', $this->data)) {
            return null;
        }

        return $this->data['dateModified'];
    }

    /**
     * Get the feed last-build date
     *
     * @return string|null
     */
    public function getLastBuildDate()
    {
        if (!array_key_exists('lastBuildDate', $this->data)) {
            return null;
        }

        return $this->data['lastBuildDate'];
    }

    /**
     * Get the feed description
     *
     * @return string|null
     */
    public function getDescription()
    {
        if (!array_key_exists('description', $this->data)) {
            return null;
        }

        return $this->data['description'];
    }

    /**
     * Get the feed generator entry
     *
     * @return string|null
     */
    public function getGenerator()
    {
        if (!array_key_exists('generator', $this->data)) {
            return null;
        }

        return $this->data['generator'];
    }

    /**
     * Get the feed ID
     *
     * @return string|null
     */
    public function getId()
    {
        if (!array_key_exists('id', $this->data)) {
            return null;
        }

        return $this->data['id'];
    }

    /**
     * Get the feed image URI
     *
     * @return array
     */
    public function getImage()
    {
        if (!array_key_exists('image', $this->data)) {
            return null;
        }

        return $this->data['image'];
    }

    /**
     * Get the feed language
     *
     * @return string|null
     */
    public function getLanguage()
    {
        if (!array_key_exists('language', $this->data)) {
            return null;
        }

        return $this->data['language'];
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
     * Get a link to the XML feed
     *
     * @return string|null
     */
    public function getFeedLinks()
    {
        if (!array_key_exists('feedLinks', $this->data)) {
            return null;
        }

        return $this->data['feedLinks'];
    }

    /**
     * Get the feed title
     *
     * @return string|null
     */
    public function getTitle()
    {
        if (!array_key_exists('title', $this->data)) {
            return null;
        }

        return $this->data['title'];
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
     * Get the feed's base url
     *
     * @return string|null
     */
    public function getBaseUrl()
    {
        if (!array_key_exists('baseUrl', $this->data)) {
            return null;
        }

        return $this->data['baseUrl'];
    }

    /**
     * Get the URLs used as Pubsubhubbub hubs endpoints
     *
     * @return string|null
     */
    public function getHubs()
    {
        if (!array_key_exists('hubs', $this->data)) {
            return null;
        }

        return $this->data['hubs'];
    }

    /**
     * Get the feed categories
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
     * Resets the instance and deletes all data
     *
     * @return void
     */
    public function reset()
    {
        $this->data = array();
    }

    /**
     * Set the current feed type being exported to "rss" or "atom". This allows
     * other objects to gracefully choose whether to execute or not, depending
     * on their appropriateness for the current type, e.g. renderers.
     *
     * @param string $type
     * @return Syndication\Feed
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Retrieve the current or last feed type exported.
     *
     * @return string Value will be "rss" or "atom"
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Unset a specific data point
     *
     * @param string $name
     * @return Syndication\Feed
     */
    public function remove($name)
    {
        if (isset($this->data[$name])) {
            unset($this->data[$name]);
        }

        return $this;
    }

    /**
     * Appends a Syndication\Feed\Entry object representing a new entry/item
     * the feed data container's internal group of entries.
     *
     * @param Syndication\Feed\Entry $entry
     * @return Syndication\Feed
     */
    public function addEntry(Entry $entry)
    {
        $entry->setEncoding($this->getEncoding());
        $this->entries[] = $entry;

        return $this;
    }

    /**
     * Removes a specific indexed entry from the internal queue. Entries must be
     * added to a feed container in order to be indexed.
     *
     * @param integer $index
     * @throws InvalidArgumentException
     * @return Syndication\Feed
     */
    public function removeEntry($index)
    {
        if (!isset($this->entries[$index])) {
            throw new InvalidArgumentException(
                'Undefined index: ' . $index . '. Entry does not exist.'
            );
        }

        unset($this->entries[$index]);

        return $this;
    }

    /**
     * Retrieve a specific indexed entry from the internal queue. Entries must be
     * added to a feed container in order to be indexed.
     *
     * @param integer $index
     * @throws InvalidArgumentException
     */
    public function getEntry($index = 0)
    {
        if (isset($this->entries[$index])) {
            return $this->entries[$index];
        }

        throw new InvalidArgumentException(
            'Undefined index: ' . $index . '. Entry does not exist.'
        );
    }

    /**
     * Orders all indexed entries by date, thus offering date ordered readable
     * content where a parser (or Homo Sapien) ignores the generic rule that
     * XML element order is irrelevant and has no intrinsic meaning.
     *
     * Using this method will alter the original indexation.
     *
     * @return Syndication\Feed
     */
    public function orderByDate()
    {
        /**
         * Could do with some improvement for performance perhaps
         */
        $timestamp = time();
        $entries = array();

        foreach ($this->entries as $entry) {
            if ($entry->getDateModified()) {
                $timestamp = (int) $entry->getDateModified()->getTimestamp();
            } elseif ($entry->getDateCreated()) {
                $timestamp = (int) $entry->getDateCreated()->getTimestamp();
            }
            $entries[$timestamp] = $entry;
        }

        krsort($entries, SORT_NUMERIC);

        $this->entries = array_values($entries);

        return $this;
    }

    /**
     * Get the number of feed entries.
     * Required by the Iterator interface.
     *
     * @return int
     */
    public function count()
    {
        return count($this->entries);
    }

    /**
     * Return the current entry
     *
     * @return Syndication\Feed\Entry
     */
    public function current()
    {
        return $this->entries[$this->key()];
    }

    /**
     * Return the current feed key
     *
     * @return mixed
     */
    public function key()
    {
        return $this->entriesKey;
    }

    /**
     * Move the feed pointer forward
     *
     * @return void
     */
    public function next()
    {
        ++$this->entriesKey;
    }

    /**
     * Reset the pointer in the feed object
     *
     * @return void
     */
    public function rewind()
    {
        $this->entriesKey = 0;
    }

    /**
     * Check to see if the iterator is still valid
     *
     * @return bool
     */
    public function valid()
    {
        return 0 <= $this->entriesKey && $this->entriesKey < $this->count();
    }

    /**
     * Export feed to Rss or Atom
     * 
     * @param  string $type
     * @return string
     */
    public function export($type)
    {
        switch (strtolower($type)) {
            case 'atom':
                return (new Writer\Atom($this))->render();
            case 'rss':
                return (new Writer\Rss($this))->render();
            default:
                throw new InvalidArgumentException('');
        }
    }
}