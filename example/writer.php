<?php

namespace Writer;

require __DIR__ . '/../vendor/autoload.php';

use Syndication;

$feed = new Syndication\Feed;
$feed->setTitle('Test');
$feed->setLink('http://www.domain.com/');
$feed->setFeedLink('http://www.domain.com/atom', 'atom');
$feed->setFeedLink('http://www.domain.com/rss', 'rss');
$feed->setDescription('bla bla bla');
$feed->setCopyright('Axel Etcheverry');
$feed->addAuthor(array(
    'name' => 'Axel Etcheverry',
    'email' => 'axel@etcheverry.biz',
    'uri'   => 'http://twitter.com/euskadi31'
));
$feed->setLanguage('fr-FR');
$feed->setDateCreated(time());

$entry = new Syndication\Feed\Entry;
$entry->setTitle('Item 1');
$entry->setDescription('bla bla');
$entry->setContent('bla bla bla lba lbal bsd df dfg dfgdfgd');
$entry->setLink('http://www.domain.com/blog/post/123');
$entry->setDateCreated(time());

$feed->addEntry($entry);

file_put_contents(__DIR__ . '/rss.xml', $feed->export('rss'));

file_put_contents(__DIR__ . '/atom.xml', $feed->export('atom'));