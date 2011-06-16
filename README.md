# Lonely Cat (alt)

A category dropdown that will only allow you to select 1 category for an entry.

### Info

Developed by Wouter Vervloet, http://www.baseworks.nl/

**This version was specifically made for [Christopher Kennedy](http://twitter.com/#!/onebrightlight/). It disallows you to select parent categories.**


### Installation

Drop the entire lonely\_cat folder in the third_party folder:

    /system/expressionengine/third_party/lonely_cat/


### Usage

Just like any other fieldtype except that Lonely Cat doesn't have any settings. It will show the categories that are assigned to that particular channel. Nothing more, nothing less...

As of Lonely Cat v 1.0 you can also output basic category information without using the {categories} tag.

    The following options are available:
    
    {field_name} => Category id
    {field_name:category_id} => Category id
    {field_name:category_name} => Category name
    {field_name:category_group} => Category group id
    {field_name:category_parent} => Category parent id
    {field_name:category_url_title} => Category URL title
    {field_name:category_description} => Category description
    {field_name:category_image} => Category image