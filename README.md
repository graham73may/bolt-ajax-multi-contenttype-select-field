# AJAX Multi-contenttype select field

This adds a new field with a type `ajaxmultictselect` that can be used to improve backend load times and allow searching and selection across multiple contenttypes. 

When a site has thousands of pieces of content and pages for example have a large number of select fields, a normal select field would populate the drop down on load. This in turn will query the database and produce a `select` field with thousands of `<option>`s populated with your content. 

To add the field install this extension on your Bolt install and then modify your `contenttypes.yml` file to add a new field, something like this:

### Single select, multiple contenttypes
Basic usage example, with the ajax field searching a number of content types. 

```yaml
featured_item:
    label: "Featured item"
    autocomplete: true
    type: ajaxmultictselect
    values: (news,blog,project,research)/contenttype,title        
```

### Multiple select, sortable
The field uses Bolts select field functionality which means it benefits from all of the same nice UI features such as multiple selection, sortable items, required, grouping, autocomplete, etc. 
```yaml
authors:
    autocomplete: true
    group: "Meta"
    label: "Author(s)"
    multiple: true
    required: true
    sortable: true
    type: ajaxmultictselect
    values: person/title
```

### Inside a repeater, with a filter applied
The field works inside a repeater. 

The filter value allows you to build up the *where* part of a setcontent tag. 

In this example:
 - `event_start` is a date field 
 - `eventtypes` is a taxonomy
 - `parent` is a hierarchical parent field - blank meaning a root event. 
 
```yaml
    upcoming_events:
        group: "Featured content in upcoming events"
        label: "Featured Items"
        limit: 6
        prefix: "<p>Please select up to a maximum of 6 items.</p>"
        type: repeater
        fields:
            item:
                label: "Event/Event series"
                autocomplete: true
                type: ajaxmultictselect
                values: (event,eventseries,summits)/contenttype,title
                filter: { event_start: '>=today', eventtypes: '!conference', 'parent': '' }
            size:
                label: "Card size"
                type: select
                values: { 'small': 'Small', 'medium': 'Mediu', 'large': 'Large'}
  
```

