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

        case 'get_hero_slide':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Slide ID is required']);
                exit;
            }
            $slide = getHeroSlide($id);
            echo json_encode(['success' => true, 'slide' => $slide]);
            break;

        case 'get_benefit':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Benefit ID is required']);
                exit;
            }
            $benefit = getBenefit($id);
            echo json_encode(['success' => true, 'benefit' => $benefit]);
            break;

        case 'get_partner':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Partner ID is required']);
                exit;
            }
            $partner = getPartner($id);
            echo json_encode(['success' => true, 'partner' => $partner]);
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

        case 'upload_page_image':
            $page = $_POST['page'] ?? null;
            $section = $_POST['section'] ?? null;
            $id = $_POST['id'] ?? null;
            $name = $_POST['name'] ?? null;

            if (!$page || !$section || empty($_FILES['image']['tmp_name'])) {
                echo json_encode(['success' => false, 'message' => 'Page name, section, and image are required']);
                exit;
            }

            $result = uploadPageImage($page, $section, $_FILES['image'], $id, $name);
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
    $filePath = __DIR__ . '/../../page-data/' . sanitizeFileName($page) . '/index.json';

    if (!file_exists($filePath)) {
        return [];
    }

    $content = file_get_contents($filePath);
    return json_decode($content, true) ?: [];
}

function getHeroSlide($id)
{
    $homepageData = getPageContent('homepage');
    $heroSlides = $homepageData['heroSlides'] ?? [];

    foreach ($heroSlides as $slide) {
        if ($slide['id'] == $id) {
            return $slide;
        }
    }

    return null;
}

function getBenefit($id)
{
    $homepageData = getPageContent('homepage');
    $keyFeatures = $homepageData['keyFeatures'] ?? [];

    foreach ($keyFeatures as $feature) {
        if ($feature['id'] == $id) {
            return $feature;
        }
    }

    return null;
}

function getPartner($id)
{
    $homepageData = getPageContent('homepage');
    $partners = $homepageData['partners'] ?? [];

    foreach ($partners as $partner) {
        if ($partner['id'] == $id) {
            return $partner;
        }
    }

    return null;
}

function saveSectionContent($page, $section, $content)
{
    try {
        $filePath = __DIR__ . '/../../page-data/' . sanitizeFileName($page) . '/index.json';
        $dirPath = dirname($filePath);

        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0777, true);
        }

        $pageContent = [];
        if (file_exists($filePath)) {
            $pageContent = json_decode(file_get_contents($filePath), true) ?: [];
        }

        switch ($section) {
            case 'heroSlide':
                return saveHeroSlide($pageContent, $content, $filePath);

            case 'heroSlideStatus':
                return updateHeroSlideStatus($pageContent, $content, $filePath);

            case 'heroSlidesOrder':
                return updateHeroSlidesOrder($pageContent, $content, $filePath);

            case 'heroSlideDelete':
                return deleteHeroSlide($pageContent, $content, $filePath);

            case 'keyFeature':
                return saveKeyFeature($pageContent, $content, $filePath);

            case 'keyFeatureStatus':
                return updateKeyFeatureStatus($pageContent, $content, $filePath);

            case 'keyFeaturesOrder':
                return updateKeyFeaturesOrder($pageContent, $content, $filePath);

            case 'keyFeatureDelete':
                return deleteKeyFeature($pageContent, $content, $filePath);

            case 'partner':
                return savePartner($pageContent, $content, $filePath);

            case 'partnerStatus':
                return updatePartnerStatus($pageContent, $content, $filePath);

            case 'partnersOrder':
                return updatePartnersOrder($pageContent, $content, $filePath);

            case 'partnerDelete':
                return deletePartner($pageContent, $content, $filePath);

            case 'requestQuoteSection':
                $pageContent['requestQuoteSection'] = $content;
                break;

            case 'featuredProductsSection':
                $pageContent['featuredProductsSection'] = $content;
                break;

            case 'categoriesSection':
                $pageContent['categoriesSection'] = $content;
                break;

            case 'partnersSection':
                $pageContent['partnersSection'] = $content;
                break;

            default:
                $pageContent[$section] = $content;
                break;
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

function saveHeroSlide($pageContent, $content, $filePath)
{
    if (!isset($pageContent['heroSlides'])) {
        $pageContent['heroSlides'] = [];
    }

    $slideId = $content['id'];
    $slideExists = false;
    $existingSlide = null;

    foreach ($pageContent['heroSlides'] as $key => $slide) {
        if ($slide['id'] == $slideId) {
            $existingSlide = $slide;

            if (isset($content['image']) && !empty($content['image'])) {

            } elseif (!isset($content['removeImage']) || !$content['removeImage']) {
                $content['image'] = $existingSlide['image'] ?? null;
            } else {
                if (!empty($existingSlide['image'])) {
                    $imagePath = __DIR__ . '/../../' . $existingSlide['image'];
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
                $content['image'] = null;
            }

            $pageContent['heroSlides'][$key] = $content;
            $slideExists = true;
            break;
        }
    }

    if (!$slideExists) {
        $pageContent['heroSlides'][] = $content;
    }

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Hero slide saved successfully'];
}

function updateHeroSlideStatus($pageContent, $content, $filePath)
{
    if (!isset($pageContent['heroSlides'])) {
        return ['success' => false, 'message' => 'No hero slides found'];
    }

    $slideId = $content['id'];
    $slideFound = false;

    foreach ($pageContent['heroSlides'] as $key => $slide) {
        if ($slide['id'] == $slideId) {
            $pageContent['heroSlides'][$key]['active'] = $content['active'];
            $slideFound = true;
            break;
        }
    }

    if (!$slideFound) {
        return ['success' => false, 'message' => 'Hero slide not found'];
    }

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Hero slide status updated successfully'];
}

function updateHeroSlidesOrder($pageContent, $content, $filePath)
{
    if (!isset($pageContent['heroSlides']) || empty($pageContent['heroSlides'])) {
        return ['success' => false, 'message' => 'No hero slides found'];
    }

    $orderMap = [];
    foreach ($content as $item) {
        $orderMap[$item['id']] = $item['order'];
    }

    foreach ($pageContent['heroSlides'] as $key => $slide) {
        if (isset($orderMap[$slide['id']])) {
            $pageContent['heroSlides'][$key]['order'] = $orderMap[$slide['id']];
        }
    }

    usort($pageContent['heroSlides'], function ($a, $b) {
        return $a['order'] - $b['order'];
    });

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Hero slides order updated successfully'];
}

function deleteHeroSlide($pageContent, $content, $filePath)
{
    if (!isset($pageContent['heroSlides'])) {
        return ['success' => false, 'message' => 'No hero slides found'];
    }

    $slideId = $content['id'];
    $slideFound = false;
    $slideImage = null;

    foreach ($pageContent['heroSlides'] as $key => $slide) {
        if ($slide['id'] == $slideId) {
            $slideImage = $slide['image'] ?? null;
            unset($pageContent['heroSlides'][$key]);
            $slideFound = true;
            break;
        }
    }

    if (!$slideFound) {
        return ['success' => false, 'message' => 'Hero slide not found'];
    }

    $pageContent['heroSlides'] = array_values($pageContent['heroSlides']);

    if ($slideImage) {
        $imagePath = __DIR__ . '/../../' . $slideImage;
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Hero slide deleted successfully'];
}

function saveKeyFeature($pageContent, $content, $filePath)
{
    if (!isset($pageContent['keyFeatures'])) {
        $pageContent['keyFeatures'] = [];
    }

    $featureId = $content['id'];
    $featureExists = false;

    foreach ($pageContent['keyFeatures'] as $key => $feature) {
        if ($feature['id'] == $featureId) {
            $pageContent['keyFeatures'][$key] = $content;
            $featureExists = true;
            break;
        }
    }

    if (!$featureExists) {
        $pageContent['keyFeatures'][] = $content;
    }

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Key feature saved successfully'];
}

function updateKeyFeatureStatus($pageContent, $content, $filePath)
{
    if (!isset($pageContent['keyFeatures'])) {
        return ['success' => false, 'message' => 'No key features found'];
    }

    $featureId = $content['id'];
    $featureFound = false;

    foreach ($pageContent['keyFeatures'] as $key => $feature) {
        if ($feature['id'] == $featureId) {
            $pageContent['keyFeatures'][$key]['active'] = $content['active'];
            $featureFound = true;
            break;
        }
    }

    if (!$featureFound) {
        return ['success' => false, 'message' => 'Key feature not found'];
    }

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Key feature status updated successfully'];
}

function updateKeyFeaturesOrder($pageContent, $content, $filePath)
{
    if (!isset($pageContent['keyFeatures']) || empty($pageContent['keyFeatures'])) {
        return ['success' => false, 'message' => 'No key features found'];
    }

    $orderMap = [];
    foreach ($content as $item) {
        $orderMap[$item['id']] = $item['order'];
    }

    foreach ($pageContent['keyFeatures'] as $key => $feature) {
        if (isset($orderMap[$feature['id']])) {
            $pageContent['keyFeatures'][$key]['order'] = $orderMap[$feature['id']];
        }
    }

    usort($pageContent['keyFeatures'], function ($a, $b) {
        return $a['order'] - $b['order'];
    });

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Key features order updated successfully'];
}

function deleteKeyFeature($pageContent, $content, $filePath)
{
    if (!isset($pageContent['keyFeatures'])) {
        return ['success' => false, 'message' => 'No key features found'];
    }

    $featureId = $content['id'];
    $featureFound = false;

    foreach ($pageContent['keyFeatures'] as $key => $feature) {
        if ($feature['id'] == $featureId) {
            unset($pageContent['keyFeatures'][$key]);
            $featureFound = true;
            break;
        }
    }

    if (!$featureFound) {
        return ['success' => false, 'message' => 'Key feature not found'];
    }

    $pageContent['keyFeatures'] = array_values($pageContent['keyFeatures']);

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Key feature deleted successfully'];
}

function savePartner($pageContent, $content, $filePath)
{
    if (!isset($pageContent['partners'])) {
        $pageContent['partners'] = [];
    }

    $partnerId = $content['id'];
    $partnerExists = false;
    $existingPartner = null;

    foreach ($pageContent['partners'] as $key => $partner) {
        if ($partner['id'] == $partnerId) {
            $existingPartner = $partner;

            if (isset($content['logo']) && !empty($content['logo'])) {

            } elseif (!isset($content['removeImage']) || !$content['removeImage']) {
                $content['logo'] = $existingPartner['logo'] ?? null;
            } else {
                if (!empty($existingPartner['logo'])) {
                    $imagePath = __DIR__ . '/../../' . $existingPartner['logo'];
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
                $content['logo'] = null;
            }

            $pageContent['partners'][$key] = $content;
            $partnerExists = true;
            break;
        }
    }

    if (!$partnerExists) {
        $pageContent['partners'][] = $content;
    }

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Partner saved successfully'];
}

function updatePartnerStatus($pageContent, $content, $filePath)
{
    if (!isset($pageContent['partners'])) {
        return ['success' => false, 'message' => 'No partners found'];
    }

    $partnerId = $content['id'];
    $partnerFound = false;

    foreach ($pageContent['partners'] as $key => $partner) {
        if ($partner['id'] == $partnerId) {
            $pageContent['partners'][$key]['active'] = $content['active'];
            $partnerFound = true;
            break;
        }
    }

    if (!$partnerFound) {
        return ['success' => false, 'message' => 'Partner not found'];
    }

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Partner status updated successfully'];
}

function updatePartnersOrder($pageContent, $content, $filePath)
{
    if (!isset($pageContent['partners']) || empty($pageContent['partners'])) {
        return ['success' => false, 'message' => 'No partners found'];
    }

    $orderMap = [];
    foreach ($content as $item) {
        $orderMap[$item['id']] = $item['order'];
    }

    foreach ($pageContent['partners'] as $key => $partner) {
        if (isset($orderMap[$partner['id']])) {
            $pageContent['partners'][$key]['order'] = $orderMap[$partner['id']];
        }
    }

    usort($pageContent['partners'], function ($a, $b) {
        return $a['order'] - $b['order'];
    });

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Partners order updated successfully'];
}

function deletePartner($pageContent, $content, $filePath)
{
    if (!isset($pageContent['partners'])) {
        return ['success' => false, 'message' => 'No partners found'];
    }

    $partnerId = $content['id'];
    $partnerFound = false;
    $partnerLogo = null;

    foreach ($pageContent['partners'] as $key => $partner) {
        if ($partner['id'] == $partnerId) {
            $partnerLogo = $partner['logo'] ?? null;
            unset($pageContent['partners'][$key]);
            $partnerFound = true;
            break;
        }
    }

    if (!$partnerFound) {
        return ['success' => false, 'message' => 'Partner not found'];
    }

    $pageContent['partners'] = array_values($pageContent['partners']);

    if ($partnerLogo) {
        $imagePath = __DIR__ . '/../../' . $partnerLogo;
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    $result = file_put_contents($filePath, json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($result === false) {
        throw new Exception('Failed to write to file: ' . $filePath);
    }

    return ['success' => true, 'message' => 'Partner deleted successfully'];
}

function savePageContent($page, $content)
{
    try {
        $filePath = __DIR__ . '/../../page-data/' . sanitizeFileName($page) . '/index.json';
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

function uploadPageImage($page, $section, $file, $id = null, $name = null)
{
    try {
        if (empty($file['tmp_name'])) {
            throw new Exception('No image file uploaded');
        }

        $pageDir = sanitizeFileName($page);
        $sectionDir = sanitizeFileName($section);
        $dir = __DIR__ . '/../../page-data/' . $pageDir . '/' . $sectionDir;

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $ext = getImageExtension($file['name']) ?: 'jpg';
        $timestamp = time();

        if ($section === 'hero') {
            $filename = 'hero_' . ($id ?: $timestamp) . '_' . $timestamp . '.' . $ext;
        } elseif ($section === 'partner') {
            $partnerName = sanitizeFileName($name ?: 'partner-' . ($id ?: $timestamp));
            $filename = $partnerName . '_' . $timestamp . '.' . $ext;
        } else {
            $filename = $sectionDir . '_' . ($id ?: $timestamp) . '_' . $timestamp . '.' . $ext;
        }

        $dest = $dir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new Exception('Failed to move uploaded file');
        }

        $imagePath = '/page-data/' . $pageDir . '/' . $sectionDir . '/' . $filename;

        return [
            'success' => true,
            'message' => 'Image uploaded successfully',
            'imagePath' => $imagePath
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error uploading image: ' . $e->getMessage()];
    }
}

function sanitizeFileName($fileName)
{
    $fileName = preg_replace('/[^a-zA-Z0-9_-]/', '', $fileName);
    return strtolower($fileName);
}

function getImageExtension($fileName)
{
    $info = pathinfo($fileName);
    if (empty($info['extension']))
        return null;
    $ext = strtolower($info['extension']);
    return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) ? $ext : null;
}
?>