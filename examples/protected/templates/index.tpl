{% comment %}

This is a comment block
(c) 2014 Guz Alexander

{% endcomment %}
<!DOCTYPE HTML>
<html>
    <head>
        {% include 'header' %}
    </head>
    <body>
		<h1>{{ document.title }}</h1>
		<p>{{ document.content }}</p>
		<p><a href="simple.php">Link to simple.php</a></p>
        {% if blog %}
        Total Blogentrys: {{ blog | size }}
        <ul id="products">
          {% for entry in blog %}
            <li>
              <h3>{{ entry.title | upcase }}</h3>
              <p>{{ entry.content }}</p>
              Comments: {{ entry.comments | size }}
                {% assign uzu = 'dudu2' %}
                {% assign freestyle = false %}

                {% for t in entry.tags %}
                    {% if t == 'freestyle' %}
                        {% assign freestyle = true %}
                    {% endif %}
                {% endfor %}

                {% if freestyle %}
                    <p>Blogentry has tag: freestyle</p>
                {% endif %}

            </li>      
          {% endfor %}
        </ul>
        {% endif %}

		{% include 'footer' %}
    </body>
</html>
