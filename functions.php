<?php declare(strict_types=1);

/**
 * More elegant way to recurse the array for this purposes
 *
 * @param array $array
 * @param array $return
 *
 * @return array
 * @see    https://davidwalsh.name/flatten-nested-arrays-php
 * @since  v1.0.0-beta.5
 */
function array_flatten(array $array, array $return): array
{
	$x = 0;
	for ($x, $xMax = count($array); $x <= $xMax; $x ++)
	{
		if (is_array($array[$x]))
		{
			$return = array_flatten($array[$x], $return);
		}
		elseif (isset($array[$x]))
		{
			$return[] = $array[$x];
		}
	}

	return $return;
}

// curl
function pull($light)
{
	$pull = curl_init();
	curl_setopt($pull, CURLOPT_URL, $light);
	curl_setopt($pull, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($pull, CURLOPT_FOLLOWLOCATION, 1);
	$emit = curl_exec($pull);
	curl_close($pull);

	return $emit;
}

// swap links
function portal($in, $out, $content)
{
	// input_url -> output_url
	$content = (!empty($out)
	    ? str_ireplace($in, $out, $content)
	    : $content
	);

	return $content;
}

// get links to assets
function tidal_disruption($data, $elements, $attribute): array
{

	$doc = new \DOMDocument();
	@$doc->loadHTML($data);
	$links = [];
	foreach ($doc->getElementsByTagName($elements) as $element)
	{
		if ($element->getAttribute('rel') !== 'canonical')
		{
			$links[] = $element->getAttribute($attribute);
		}
	}

	return $links;
}

// generate files
function generate($route, $path, $data)
{
	if (!is_dir($route) && !mkdir($route, 0755, true) && !is_dir($route))
	{
		throw new \RuntimeException(sprintf('Directory "%s" was not created', $route));
	}
	file_put_contents($path, $data);
}

// generate pages
function pages($that, $route, $path, $path_origin, $data, $force, $verbose)
{

	// page exists
	if (!$force && is_file($path))
	{
		if (filemtime($path_origin) > filemtime($path))
		{
			// page was changed: copy the new one

			generate($route, $path, $data);
			if ($verbose)
			{
				$that->writeln('<green>+ Page ➜ ' . $path . ' [update existing file]</green>');
			}
		}
		elseif ($verbose)
		{
			// page doesn't exist or force option is enabled
			$that->writeln('<cyan>• Page ➜ ' . $path . ' [already exists and not updated]</cyan>');
		}
	}
	else
	{
		// copy the page
		generate($route, $path, $data);
		if ($verbose)
		{
			$that->writeln('<green>+ Page ➜ ' . $path . ' [render new file into output]</green>');
		}
	}
}

// generate assets
function assets($that, $event_horizon, $input_url, $data, $force, $verbose)
{
	$asset_links     = [];
	$asset_links[]   = tidal_disruption($data, 'link', 'href');
	$asset_links[]   = tidal_disruption($data, 'script', 'src');
	$asset_links[]   = tidal_disruption($data, 'img', 'src');
	$input_url_parts = parse_url($input_url);

	// echo 'input_url: ' . $input_url . PHP_EOL;

	foreach (array_flatten($asset_links, []) as $asset)
	{
		if (
		    strpos($asset, 'data:') !== 0 && // exclude data URIs
		    (strpos($asset, '/') === 0 || $input_url_parts['host'] === parse_url($asset)['host']) // continue if asset is local
		)
		{
			$asset_file_origin      = rtrim(GRAV_ROOT, '/') . $asset;
			$asset_file_destination = rtrim($event_horizon, '/') . $asset;
			$asset_route            = str_replace(basename($asset_file_destination), '', $asset_file_destination);
			// echo 'asset_file_origin: ' . $asset_file_origin . "\r\n";
			// echo 'asset_file_destination: ' . $asset_file_destination . "\r\n";
			// echo 'asset_route: ' . $asset_route . "\r\n";
			// echo 'asset: ' . $asset . "\r\n";
			// asset exists
			if (is_file($asset_file_destination))
			{
				switch (true)
				{
					// asset was changed: copy the new one
					case md5_file($asset_file_origin) !== md5_file($asset_file_destination):
						if (!is_dir($asset_route) && !mkdir($asset_route, 0755, true) && !is_dir($asset_route))
						{
							throw new \RuntimeException(sprintf('Directory "%s" was not created', $asset_route));
						}
						$copied = copy($asset_file_origin, $asset_file_destination);
						if ($verbose)
						{
							$that->writeln('  <green>+ Asset ➜ ' . $asset_file_destination . ' [' . ($copied ? 'copied' :
							                   'not copied') . ']</green>');
						}
						break;
					// if force, then copy it anyway
					case $force:
						$copied = copy($asset_file_origin, $asset_file_destination);
						if ($verbose)
						{
							$that->writeln('  <green>+ Asset ➜ ' . $asset_file_destination . ' [ with -f command ' . ($copied ?
							                   'copied' :
							                   'not copied') . ']</green>');
						}
						break;
					// no asset changes and not forced: skip it
					default:
						if ($verbose)
						{
							$that->writeln('  <cyan>• Asset ➜ ' . $asset_file_destination . '</cyan>');
						}
						break;
				}
				// asset doesn't exist
			}
			elseif (false !== stripos($asset, $input_url, 0))
			{
				/**
				 * @since  v1.0.0-beta.5
				 *         <link rel="alternate" type="application/rss+xml" href="http://127.0.0.1:8080/feed.rss" title="RSS Feed">
				 *         <link rel="alternate" type="application/rss+xml" href="{{--output-url}}/feed.rss" title="RSS Feed">
				 */
				$newAsset = str_replace($input_url, '', $asset);
				str_replace($asset, $newAsset, $data);
			}
			else
			{
				if (!is_dir($asset_route) && !mkdir($asset_route, 0755, true) && !is_dir($asset_route))
				{
					throw new \RuntimeException(sprintf('Directory "%s" was not created', $asset_route));
				}
				$copied = copy($asset_file_origin, $asset_file_destination);
				$that->writeln('  <green>+ Asset ➜ ' . $asset_file_destination . ' [' . ($copied ? 'copied' :
				                   'not copied') . ']</green>');
			}
		}
	}
}