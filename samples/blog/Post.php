<?php
/*
  +---------------------------------------------------------------------------------+
  | Copyright (c) 2010 ActiveMongo                                                  |
  +---------------------------------------------------------------------------------+
  | Redistribution and use in source and binary forms, with or without              |
  | modification, are permitted provided that the following conditions are met:     |
  | 1. Redistributions of source code must retain the above copyright               |
  |    notice, this list of conditions and the following disclaimer.                |
  |                                                                                 |
  | 2. Redistributions in binary form must reproduce the above copyright            |
  |    notice, this list of conditions and the following disclaimer in the          |
  |    documentation and/or other materials provided with the distribution.         |
  |                                                                                 |
  | 3. All advertising materials mentioning features or use of this software        |
  |    must display the following acknowledgement:                                  |
  |    This product includes software developed by César D. Rodas.                  |
  |                                                                                 |
  | 4. Neither the name of the César D. Rodas nor the                               |
  |    names of its contributors may be used to endorse or promote products         |
  |    derived from this software without specific prior written permission.        |
  |                                                                                 |
  | THIS SOFTWARE IS PROVIDED BY CÉSAR D. RODAS ''AS IS'' AND ANY                   |
  | EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED       |
  | WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE          |
  | DISCLAIMED. IN NO EVENT SHALL CÉSAR D. RODAS BE LIABLE FOR ANY                  |
  | DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES      |
  | (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;    |
  | LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND     |
  | ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT      |
  | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS   |
  | SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE                     |
  +---------------------------------------------------------------------------------+
  | Authors: César Rodas <crodas@php.net>                                           |
  +---------------------------------------------------------------------------------+
*/


class PostModel extends ActiveMongo
{
    static $validates_presence_of = array(
        'title',
        'uri',
        'author',
    );

    static $validates_length_of = array(
        array('title', 'min' => 5, 'max' => 30),
    );

    public $title;
    public $uri;
    public $author;
    public $comment;
    public $ts;

    const LIMIT_PER_PAGE=20;

    /**
     *  Return this collection name.
     *
     *  @return string
     */
    function getCollectionName()
    {
        return 'post';
    }

    function before_validate(&$obj)
    { 
        $obj['ts'] = new MongoTimestamp();            
    }


    /**
     *  Author Filter.
     *
     *  Check if we save the Author _id, 
     *  or it throws an exception.
     *
     *  @param mixed &$value    Current value
     *  @param mixed $old_value Past value, used on Update
     *
     *  @return bool
     */
    function author_filter(&$value, $old_value)
    {
        if (!$value instanceof MongoID) {
            throw new Exception("Invalid MongoID");
        }
        return TRUE;
    }

    /**
     *  Update Author information in the Posts. This function
     *  is trigger when a new Post is created or the Author
     *  has updated his/her profile.
     *
     *  @param MongoID $id  Author ID
     *  
     *  @return void
     */
    function updateAuthorInfo(MongoID $id)
    {
        $author = new AuthorModel;
        $author->where('id', $id);

        /* perform the query */
        $author->doQuery();

        $document = array(
            '$set' => array(
                'author_name' => $author->name,
                'author_username' => $author->username,
            ),
        );

        $filter = array(
            'author' => $id,
        );

        $this->getCollection()->update($filter, $document, array('multiple' => TRUE));

        return TRUE;
    }

    /**
     *  A new post must have its author information
     *  on it. (to avoid multiple requests at render time).
     *
     *  @return void
     */
    function after_create()
    {
        $this->updateAuthorInfo($this->author);
    }

    /**
     *  Simple abstraction to add a new comment,
     *  to avoid typo errors at coding time.
     */
    function add_comment($user, $email, $comment)
    {
        $this->comment[] = array(
            "user" => $user,
            "email" => $email,
            "comment" => $comment,
        );
        return TRUE;
    }

    function setup()
    {
        $this->addIndex(array('uri' => 1), array('unique'=> 1));
        $this->addIndex(array('author' => 1));
        $this->addIndex(array('ts' => -1));
    }


    function testing1()
    {
        var_dump(array('a' => 1, 'a'=>2));
        $c = $this->getCollection();
        //$d = $c->find(array('author_name' => array('$all' => array(new MongoRegex("/^c/"), new MongoRegex('/s$/')  ))));
        $d = $c->find(array('author_name' => array('$all' => array(new MongoRegex("/^c/"), new MongoRegex('/s$/')  ))));

        var_dump($d->count());
        var_dump($d->info());
    }
}


