<?php

/* Eregansu: RDF object store
 *
 * Copyright 2011 Mo McRoberts.
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

require_once(dirname(__FILE__) . '/../lib/rdf.php');
require_once(dirname(__FILE__) . '/store.php');

/* RDFStore extends Store to store RDF graphs using the JSON encoding
 * described at http://n2.talis.com/wiki/RDF_JSON_Specification
 */

class RDFStore extends Store
{
	protected $storableClass = 'RDFStoredObject';
}

class RDFStoredObject extends RDFInstance
{
	protected static $models = array();

	public static function objectForData($data, $model = null, $className = null)
	{
		if(!strlen($className))
		{
			$className = 'RDFStoredObject';
		}
		if(!isset(self::$models[$className]))
		{
			self::$models[$className] = $model;
		}
		$inst = null;
		if(isset($data[RDF::rdf.'type']))
		{
			foreach($data[RDF::rdf.'type'] as $type)
			{
				if(isset($type['type']) && $type['type'] == 'uri' && isset($type['value']))
				{
					if(($inst = RDF::instanceForClass($type['value'])))
					{
						break;
					}
				}
			}
		}
		if($inst)
		{
			$className = get_class($inst);
		}
		else
		{
			$inst = new $className();
		}
		if(!isset(self::$models[$className]))
		{
			self::$models[$className] = $model;
		}
		self::applyProperties($inst, $data, $model);
		$inst->loaded();
		return $inst;
	}

	protected static function applyProperties($inst, $data, $model)
	{
		if(!is_arrayish($data))
		{
			throw new Exception(gettype($data) . ' passed to RDFStoreObject::objectForData(), array expected');
		}
		if(!is_object($inst))
		{
			throw new Exception($inst . ' is not an object');
		}
		foreach($data as $k => $v)
		{
			if(!strlen($k)) continue;
			if(!strcmp($k, 'refcount')) continue;
			if(is_array($v))
			{
				foreach($v as $pk => $s)
				{
					if(is_array($s) && isset($s['type']))
					{
						switch($s['type'])
						{
						case 'uri':
							if(isset($s['value']) && is_string($s['value']))
							{
								$v[$pk] = new RDFURI($s['value']);
							}
							else
							{
								unset($v[$pk]);
							}
							break;
						case 'literal':
							if(isset($s['lang']) || isset($s['datatype']))
							{
								$l = RDFComplexLiteral::literal(isset($s['datatype']) ? $s['datatype'] : null, $s['value']);
								if(isset($s['lang']))
								{
									$l->{RDF::xml . ' lang'}[] = $s['lang'];
								}
								$v[$pk] = $l;
							}
							else
							{
								$v[$pk] = $s['value'];
							}
							break;
						case 'bnode':
							if(!strncmp($s['value'], '_:', 2))
							{
								$s['value'] = '#' . substr($s['value'], 2);
							}
							$v[$pk] = new RDFURI($s['value']);
							break;
						case 'node':
							$v[$pk] = RDFStoredObject::objectForData($s['value'], $model);
							$v[$pk]->refcount++;
							break;
						default:
							/* XXX this should go away */
							if(isset($s['value']))
							{
								$v[$pk] = $s['value'];
							}
//							echo '<pre>'; print_r($v); echo '</pre>';
//							throw new Exception('unhandled RDF/JSON type ' . $s['type']);
						}
					}
				}
			}
			$inst->{$k} = $v;
		}
	}
	
	protected function loaded($reloaded = false)
	{
	}
}
