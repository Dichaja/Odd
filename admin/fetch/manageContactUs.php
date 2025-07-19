<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');
date_default_timezone_set('Africa/Kampala');

$inputData = file_get_contents('php://input');
$requestMethod = $_SERVER['REQUEST_METHOD'];
$action = '';

if ($requestMethod === 'GET') {
    $action = $_GET['action'] ?? '';
} elseif ($requestMethod === 'POST') {
    $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

    if (strpos($contentType, 'application/json') !== false) {
        $jsonData = json_decode($inputData, true);
        $action = $jsonData['action'] ?? '';
    } else {
        $action = $_POST['action'] ?? '';
    }
}

try {
    switch ($action) {
        case 'get_page_content':
            $page = $_GET['page'] ?? null;
            if (!$page) {
                echo json_encode(['success' => false, 'message' => 'Page name is required']);
                exit;
            }
            $content = getPageContent($page);
            echo json_encode(['success' => true, 'content' => $content]);
            break;

        case 'get_phone':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Phone ID is required']);
                exit;
            }
            $phone = getPhone($id);
            echo json_encode(['success' => true, 'phone' => $phone]);
            break;

        case 'get_email':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Email ID is required']);
                exit;
            }
            $email = getEmail($id);
            echo json_encode(['success' => true, 'email' => $email]);
            break;

        case 'get_location':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Location ID is required']);
                exit;
            }
            $location = getLocation($id);
            echo json_encode(['success' => true, 'location' => $location]);
            break;

        case 'get_social':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Social ID is required']);
                exit;
            }
            $social = getSocial($id);
            echo json_encode(['success' => true, 'social' => $social]);
            break;

        case 'get_field':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Field ID is required']);
                exit;
            }
            $field = getField($id);
            echo json_encode(['success' => true, 'field' => $field]);
            break;

        case 'save_section_content':
            $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

            if (strpos($contentType, 'application/json') !== false) {
                $data = json_decode($inputData, true);
            } else {
                $data = $_POST;
            }

            $page = $data['page'] ?? null;
            $section = $data['section'] ?? null;
            $content = $data['content'] ?? null;

            if (!$page || !$section || !$content) {
                echo json_encode(['success' => false, 'message' => 'Page name, section, and content are required']);
                exit;
            }

            $result = saveSectionContent($page, $section, $content);
            echo json_encode($result);
            break;

        case 'save_page_content':
            $data = json_decode($inputData, true);
            if (!$data) {
                $data = $_POST;
            }

            $page = $data['page'] ?? null;
            $content = $data['content'] ?? null;

            if (!$page || !$content) {
                echo json_encode(['success' => false, 'message' => 'Page name and content are required']);
                exit;
            }

            $result = savePageContent($page, $content);
            echo json_encode($result);
            break;

        default:
            $debug = [
                'action' => $action,
                'method' => $requestMethod,
                'contentType' => $_SERVER['CONTENT_TYPE'] ?? 'none',
                'postData' => $_POST,
                'getData' => $_GET,
                'rawInput' => substr($inputData, 0, 1000)
            ];

            echo json_encode([
                'success' => false,
                'message' => 'Invalid action',
                'debug' => $debug
            ]);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function getPageContent($page)
{
    $filePath = __DIR__ . '/../../page-data/' . sanitizeFileName($page) . '/contact-us.json';

    if (!file_exists($filePath)) {
        return [];
    }

    $content = file_get_contents($filePath);
    return json_decode($content, true) ?: [];
}

function getPhone($id)
{
    $contactData = getPageContent('contact-us');
    $phones = $contactData['contactInfo']['phones'] ?? [];

    foreach ($phones as $phone) {
        if ($phone['id'] == $id) {
            return $phone;
        }
    }

    return null;
}

function getEmail($id)
{
    $contactData = getPageContent('contact-us');
    $emails = $contactData['contactInfo']['emails'] ?? [];

    foreach ($emails as $email) {
        if ($email['id'] == $id) {
            return $email;
        }
    }

    return null;
}

function getLocation($id)
{
    $contactData = getPageContent('contact-us');
    $locations = $contactData['contactInfo']['location'] ?? [];

    foreach ($locations as $location) {
        if ($location['id'] == $id) {
            return $location;
        }
    }

    return null;
}

function getSocial($id)
{
    $contactData = getPageContent('contact-us');
    $socials = $contactData['contactInfo']['social'] ?? [];

    foreach ($socials as $social) {
        if ($social['id'] == $id) {
            return $social;
        }
    }

    return null;
}

function getField($id)
{
    $contactData = getPageContent('contact-us');
    $fields = $contactData['formSettings']['fields'] ?? [];

    foreach ($fields as $field) {
        if ($field['id'] == $id) {
            return $field;
        }
    }

    return null;
}

function saveSectionContent($page, $section, $content)
{
    try {
        $filePath = __DIR__ . '/../../page-data/' . sanitizeFileName($page) . '/contact-us.json';
        $dirPath = dirname($filePath);

        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0777, true);
        }

        $pageContent = [];
        if (file_exists($filePath)) {
            $pageContent = json_decode(file_get_contents($filePath), true) ?: [];
        }

        if (!isset($pageContent['contactInfo'])) {
            $pageContent['contactInfo'] = [
                'phones' => [],
                'emails' => [],
                'location' => [],
                'social' => []
            ];
        }

        if (!isset($pageContent['formSettings'])) {
            $pageContent['formSettings'] = [
                'title' => 'Get in Touch',
                'description' => '',
                'buttonText' => 'Send Message',
                'fields' => [],
                'mapCoordinates' => [
                    'latitude' => '0.31654191425996444',
                    'longitude' => '32.629696775378866',
                    'zoom' => 15
                ]
            ];
        }

        switch ($section) {
            case 'phone':
                return savePhone($pageContent, $content, $filePath);

            case 'phoneStatus':
                return updatePhoneStatus($pageContent, $content, $filePath);

            case 'phonesOrder':
                return updatePhonesOrder($pageContent, $content, $filePath);

            case 'phoneDelete':
                return deletePhone($pageContent, $content, $filePath);

            case 'email':
                return saveEmail($pageContent, $content, $filePath);

            case 'emailStatus':
                return updateEmailStatus($pageContent, $content, $filePath);

            case 'emailsOrder':
                return updateEmailsOrder($pageContent, $content, $filePath);

            case 'emailDelete':
                return deleteEmail($pageContent, $content, $filePath);

            case 'location':
                return saveLocation($pageContent, $content, $filePath);

            case 'locationStatus':
                return updateLocationStatus($pageContent, $content, $filePath);

            case 'locationOrder':
                return updateLocationOrder($pageContent, $content, $filePath);

            case 'locationDelete':
                return deleteLocation($pageContent, $content, $filePath);

            case 'social':
                return saveSocial($pageContent, $content, $filePath);

            case 'socialStatus':
                return updateSocialStatus($pageContent, $content, $filePath);

            case 'socialOrder':
                return updateSocialOrder($pageContent, $content, $filePath);

            case 'field':
                return saveField($pageContent, $content, $filePath);

            case 'fieldsOrder':
                return updateFieldsOrder($pageContent, $content, $filePath);

            case 'fieldDelete':
                return deleteField($pageContent, $content, $filePath);

            case 'formTitle':
                $pageContent['formSettings']['title'] = $content['title'];
                $pageContent['formSettings']['description'] = $content['description'];
                $pageContent['formSettings']['buttonText'] = $content['buttonText'];
                break;

            case 'mapSettings':
                $pageContent['formSettings']['mapCoordinates'] = [
                    'latitude' => $content['latitude'],
                    'longitude' => $content['longitude'],
                    'zoom' => $content['zoom']
                ];
                break;

            default:
                return ['success' => false, 'message' => 'Invalid section: ' . $section];
        }

        $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        if ($result === false) {
            throw new Exception('Failed to write to file: ' . $filePath);
        }

        return ['success' => true, 'message' => 'Section content saved successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error saving section content: ' . $e->getMessage()];
    }
}

function savePhone($pageContent, $content, $filePath)
{
    if (!isset($pageContent['contactInfo']['phones'])) {
        $pageContent['contactInfo']['phones'] = [];
    }

    $phoneId = $content['id'];
    $phoneExists = false;

    foreach ($pageContent['contactInfo']['phones'] as $key => $phone) {
        if ($phone['id'] == $phoneId) {
            $pageContent['contactInfo']['phones'][$key] = $content;
            $phoneExists = true;
            break;
        }
    }

    if (!$phoneExists) {
        $pageContent['contactInfo']['phones'][] = $content;
    }

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Phone saved successfully'];
}

function updatePhoneStatus($pageContent, $content, $filePath)
{
    if (!isset($pageContent['contactInfo']['phones'])) {
        return ['success' => false, 'message' => 'No phones found'];
    }

    $phoneId = $content['id'];
    $phoneFound = false;

    foreach ($pageContent['contactInfo']['phones'] as $key => $phone) {
        if ($phone['id'] == $phoneId) {
            $pageContent['contactInfo']['phones'][$key]['active'] = $content['active'];
            $phoneFound = true;
            break;
        }
    }

    if (!$phoneFound) {
        return ['success' => false, 'message' => 'Phone not found'];
    }

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Phone status updated successfully'];
}

function updatePhonesOrder($pageContent, $content, $filePath)
{
    if (!isset($pageContent['contactInfo']['phones']) || empty($pageContent['contactInfo']['phones'])) {
        return ['success' => false, 'message' => 'No phones found'];
    }

    $orderMap = [];
    foreach ($content as $item) {
        $orderMap[$item['id']] = $item['order'];
    }

    foreach ($pageContent['contactInfo']['phones'] as $key => $phone) {
        if (isset($orderMap[$phone['id']])) {
            $pageContent['contactInfo']['phones'][$key]['order'] = $orderMap[$phone['id']];
        }
    }

    usort($pageContent['contactInfo']['phones'], function ($a, $b) {
        return $a['order'] - $b['order'];
    });

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Phones order updated successfully'];
}

function deletePhone($pageContent, $content, $filePath)
{
    if (!isset($pageContent['contactInfo']['phones'])) {
        return ['success' => false, 'message' => 'No phones found'];
    }

    $phoneId = $content['id'];
    $phoneFound = false;

    foreach ($pageContent['contactInfo']['phones'] as $key => $phone) {
        if ($phone['id'] == $phoneId) {
            unset($pageContent['contactInfo']['phones'][$key]);
            $phoneFound = true;
            break;
        }
    }

    if (!$phoneFound) {
        return ['success' => false, 'message' => 'Phone not found'];
    }

    $pageContent['contactInfo']['phones'] = array_values($pageContent['contactInfo']['phones']);

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Phone deleted successfully'];
}

function saveEmail($pageContent, $content, $filePath)
{
    if (!isset($pageContent['contactInfo']['emails'])) {
        $pageContent['contactInfo']['emails'] = [];
    }

    $emailId = $content['id'];
    $emailExists = false;

    foreach ($pageContent['contactInfo']['emails'] as $key => $email) {
        if ($email['id'] == $emailId) {
            $pageContent['contactInfo']['emails'][$key] = $content;
            $emailExists = true;
            break;
        }
    }

    if (!$emailExists) {
        $pageContent['contactInfo']['emails'][] = $content;
    }

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Email saved successfully'];
}

function updateEmailStatus($pageContent, $content, $filePath)
{
    if (!isset($pageContent['contactInfo']['emails'])) {
        return ['success' => false, 'message' => 'No emails found'];
    }

    $emailId = $content['id'];
    $emailFound = false;

    foreach ($pageContent['contactInfo']['emails'] as $key => $email) {
        if ($email['id'] == $emailId) {
            $pageContent['contactInfo']['emails'][$key]['active'] = $content['active'];
            $emailFound = true;
            break;
        }
    }

    if (!$emailFound) {
        return ['success' => false, 'message' => 'Email not found'];
    }

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Email status updated successfully'];
}

function updateEmailsOrder($pageContent, $content, $filePath)
{
    if (!isset($pageContent['contactInfo']['emails']) || empty($pageContent['contactInfo']['emails'])) {
        return ['success' => false, 'message' => 'No emails found'];
    }

    $orderMap = [];
    foreach ($content as $item) {
        $orderMap[$item['id']] = $item['order'];
    }

    foreach ($pageContent['contactInfo']['emails'] as $key => $email) {
        if (isset($orderMap[$email['id']])) {
            $pageContent['contactInfo']['emails'][$key]['order'] = $orderMap[$email['id']];
        }
    }

    usort($pageContent['contactInfo']['emails'], function ($a, $b) {
        return $a['order'] - $b['order'];
    });

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Emails order updated successfully'];
}

function deleteEmail($pageContent, $content, $filePath)
{
    if (!isset($pageContent['contactInfo']['emails'])) {
        return ['success' => false, 'message' => 'No emails found'];
    }

    $emailId = $content['id'];
    $emailFound = false;

    foreach ($pageContent['contactInfo']['emails'] as $key => $email) {
        if ($email['id'] == $emailId) {
            unset($pageContent['contactInfo']['emails'][$key]);
            $emailFound = true;
            break;
        }
    }

    if (!$emailFound) {
        return ['success' => false, 'message' => 'Email not found'];
    }

    $pageContent['contactInfo']['emails'] = array_values($pageContent['contactInfo']['emails']);

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Email deleted successfully'];
}

function saveLocation($pageContent, $content, $filePath)
{
    if (!isset($pageContent['contactInfo']['location'])) {
        $pageContent['contactInfo']['location'] = [];
    }

    $locationId = $content['id'];
    $locationExists = false;

    foreach ($pageContent['contactInfo']['location'] as $key => $location) {
        if ($location['id'] == $locationId) {
            $pageContent['contactInfo']['location'][$key] = $content;
            $locationExists = true;
            break;
        }
    }

    if (!$locationExists) {
        $pageContent['contactInfo']['location'][] = $content;
    }

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Location saved successfully'];
}

function updateLocationStatus($pageContent, $content, $filePath)
{
    if (!isset($pageContent['contactInfo']['location'])) {
        return ['success' => false, 'message' => 'No locations found'];
    }

    $locationId = $content['id'];
    $locationFound = false;

    foreach ($pageContent['contactInfo']['location'] as $key => $location) {
        if ($location['id'] == $locationId) {
            $pageContent['contactInfo']['location'][$key]['active'] = $content['active'];
            $locationFound = true;
            break;
        }
    }

    if (!$locationFound) {
        return ['success' => false, 'message' => 'Location not found'];
    }

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Location status updated successfully'];
}

function updateLocationOrder($pageContent, $content, $filePath)
{
    if (!isset($pageContent['contactInfo']['location']) || empty($pageContent['contactInfo']['location'])) {
        return ['success' => false, 'message' => 'No locations found'];
    }

    $orderMap = [];
    foreach ($content as $item) {
        $orderMap[$item['id']] = $item['order'];
    }

    foreach ($pageContent['contactInfo']['location'] as $key => $location) {
        if (isset($orderMap[$location['id']])) {
            $pageContent['contactInfo']['location'][$key]['order'] = $orderMap[$location['id']];
        }
    }

    usort($pageContent['contactInfo']['location'], function ($a, $b) {
        return $a['order'] - $b['order'];
    });

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Location order updated successfully'];
}

function deleteLocation($pageContent, $content, $filePath)
{
    if (!isset($pageContent['contactInfo']['location'])) {
        return ['success' => false, 'message' => 'No locations found'];
    }

    $locationId = $content['id'];
    $locationFound = false;

    foreach ($pageContent['contactInfo']['location'] as $key => $location) {
        if ($location['id'] == $locationId) {
            unset($pageContent['contactInfo']['location'][$key]);
            $locationFound = true;
            break;
        }
    }

    if (!$locationFound) {
        return ['success' => false, 'message' => 'Location not found'];
    }

    $pageContent['contactInfo']['location'] = array_values($pageContent['contactInfo']['location']);

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Location deleted successfully'];
}

function saveSocial($pageContent, $content, $filePath)
{
    if (!isset($pageContent['contactInfo']['social'])) {
        $pageContent['contactInfo']['social'] = [];
    }

    $socialId = $content['id'];
    $socialExists = false;

    foreach ($pageContent['contactInfo']['social'] as $key => $social) {
        if ($social['id'] == $socialId) {
            $pageContent['contactInfo']['social'][$key] = $content;
            $socialExists = true;
            break;
        }
    }

    if (!$socialExists) {
        $pageContent['contactInfo']['social'][] = $content;
    }

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Social link saved successfully'];
}

function updateSocialStatus($pageContent, $content, $filePath)
{
    if (!isset($pageContent['contactInfo']['social'])) {
        return ['success' => false, 'message' => 'No social links found'];
    }

    $socialId = $content['id'];
    $socialFound = false;

    foreach ($pageContent['contactInfo']['social'] as $key => $social) {
        if ($social['id'] == $socialId) {
            $pageContent['contactInfo']['social'][$key]['active'] = $content['active'];
            $socialFound = true;
            break;
        }
    }

    if (!$socialFound) {
        return ['success' => false, 'message' => 'Social link not found'];
    }

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Social link status updated successfully'];
}

function updateSocialOrder($pageContent, $content, $filePath)
{
    if (!isset($pageContent['contactInfo']['social']) || empty($pageContent['contactInfo']['social'])) {
        return ['success' => false, 'message' => 'No social links found'];
    }

    $orderMap = [];
    foreach ($content as $item) {
        $orderMap[$item['id']] = $item['order'];
    }

    foreach ($pageContent['contactInfo']['social'] as $key => $social) {
        if (isset($orderMap[$social['id']])) {
            $pageContent['contactInfo']['social'][$key]['order'] = $orderMap[$social['id']];
        }
    }

    usort($pageContent['contactInfo']['social'], function ($a, $b) {
        return $a['order'] - $b['order'];
    });

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Social links order updated successfully'];
}

function saveField($pageContent, $content, $filePath)
{
    if (!isset($pageContent['formSettings']['fields'])) {
        $pageContent['formSettings']['fields'] = [];
    }

    $fieldId = $content['id'];
    $fieldExists = false;

    foreach ($pageContent['formSettings']['fields'] as $key => $field) {
        if ($field['id'] == $fieldId) {
            $pageContent['formSettings']['fields'][$key] = $content;
            $fieldExists = true;
            break;
        }
    }

    if (!$fieldExists) {
        $pageContent['formSettings']['fields'][] = $content;
    }

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Form field saved successfully'];
}

function updateFieldsOrder($pageContent, $content, $filePath)
{
    if (!isset($pageContent['formSettings']['fields']) || empty($pageContent['formSettings']['fields'])) {
        return ['success' => false, 'message' => 'No form fields found'];
    }

    $orderMap = [];
    foreach ($content as $item) {
        $orderMap[$item['id']] = $item['order'];
    }

    foreach ($pageContent['formSettings']['fields'] as $key => $field) {
        if (isset($orderMap[$field['id']])) {
            $pageContent['formSettings']['fields'][$key]['order'] = $orderMap[$field['id']];
        }
    }

    usort($pageContent['formSettings']['fields'], function ($a, $b) {
        return $a['order'] - $b['order'];
    });

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Form fields order updated successfully'];
}

function deleteField($pageContent, $content, $filePath)
{
    if (!isset($pageContent['formSettings']['fields'])) {
        return ['success' => false, 'message' => 'No form fields found'];
    }

    $fieldId = $content['id'];
    $fieldFound = false;

    foreach ($pageContent['formSettings']['fields'] as $key => $field) {
        if ($field['id'] == $fieldId) {
            unset($pageContent['formSettings']['fields'][$key]);
            $fieldFound = true;
            break;
        }
    }

    if (!$fieldFound) {
        return ['success' => false, 'message' => 'Form field not found'];
    }

    $pageContent['formSettings']['fields'] = array_values($pageContent['formSettings']['fields']);

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Form field deleted successfully'];
}

function savePageContent($page, $content)
{
    try {
        $filePath = __DIR__ . '/../../page-data/' . sanitizeFileName($page) . '/contact-us.json';
        $dirPath = dirname($filePath);

        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0777, true);
        }

        $result = file_put_contents($filePath, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        if ($result === false) {
            throw new Exception('Failed to write to file: ' . $filePath);
        }

        return ['success' => true, 'message' => 'Page content saved successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error saving page content: ' . $e->getMessage()];
    }
}

function sanitizeFileName($fileName)
{
    $fileName = preg_replace('/[^a-zA-Z0-9_-]/', '', $fileName);
    return strtolower($fileName);
}
?>