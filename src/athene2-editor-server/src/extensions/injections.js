/**
 * Serlo Flavored Markdown
 * Injections:
 * Transforms >[Title](injectionUrl)
 * into <div class="injection"><a href="injectionUrl" class="injection-link">Title</a></div>
 **/
var injections = function() {
  var filter,
    findInjections = new RegExp(/>\[(.*)\]\((.*)\)/g)

  // Corrects relative urls with missing leading slash
  function correctUrl(url) {
    url = url.split('/')
    // Url does start with http
    if (url[0] === 'http:' || url[0] === 'https:') {
      // is invalid for injections, but do nothing
      return url.join('/')
    }

    // first item is empty, means there already is a leading slash
    if (url[0] === '') {
      url.shift()
    }

    // Url does not start with / or http
    return '/' + url.join('/')
  }

  filter = function(text) {
    return text.replace(findInjections, function(original, title, url) {
      return (
        '<div class="injection"><a href="' +
        correctUrl(url) +
        '" class="injection-link">' +
        title +
        '</a></div>'
      )
    })
  }

  return [
    {
      type: 'lang',
      filter: filter
    }
  ]
}

module.exports = injections
