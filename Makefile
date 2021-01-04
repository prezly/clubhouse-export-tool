.PHONY: all

all: data/clubhouse.min.json data/clubhouse.pretty.json data/clubhouse.sample.json

data/clubhouse.pretty.json: data/clubhouse.json
	cat data/clubhouse.json \
		| php -r 'echo json_encode(json_decode(stream_get_contents(STDIN)), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);' \
		> data/clubhouse.pretty.json

data/clubhouse.min.json: data/clubhouse.json
	cat data/clubhouse.json \
		| php -r 'echo  json_encode(json_decode(stream_get_contents(STDIN)), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);' \
		> data/clubhouse.min.json

data/clubhouse.sample.json: data/clubhouse.json
	cat data/clubhouse.json \
		| php -r '$$data = json_decode(stream_get_contents(STDIN)); $$data->stories = array_values(array_filter($$data->stories, fn ($$s) => $$s->id % 100 <= 5)); echo json_encode($$data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);' \
		> data/clubhouse.sample.json

data/clubhouse.json: vendor
	php export.php > data/clubhouse.json

vendor: composer.json composer.lock
	composer install
