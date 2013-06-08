<?php

/* Copyright 2012-2013 Mo McRoberts.
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

class BuiltinTestsuiteModuleInstall extends BuiltinModuleInstaller
{
	public $coexists = true;

	public function writeAppConfig($file, $isSoleWebModule = false, $chosenSoleWebModule = null)
	{
        fwrite($file, "\$EREGANSU_MODULES['testsuite'] = PLATFORM_ROOT . 'testsuite/testsuite.php';\n");
        fwrite($file, "\$EREGANSU_MODULES['harness'] = PLATFORM_ROOT . 'testsuite/harness.php';\n");
		fwrite($file, "\$AUTOLOAD['testsuite'] = PLATFORM_ROOT . 'testsuite/testsuite.php';\n");
		fwrite($file, "\$AUTOLOAD['testharness'] = PLATFORM_ROOT . 'testsuite/harness.php';\n");
	}
}
