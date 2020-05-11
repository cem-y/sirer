<?php
namespace DareOne\operations;
use DareOne\models\bib\BibEntry;
use DareOne\models\sources\DocMarker;
use DareOne\models\sources\DocPage;
use DareOne\models\sources\Document;
use DareOne\models\fulltext\FulltextBase;
use DareOne\models\fulltext\FulltextSection;
use DareOne\models\works\Work;
use DareOne\models\works\WorkAverroes;
use DareOne\operations\manuscripts\ManuscriptTools;
use DareOne\operations\utilities\DocumentTools;
use Illuminate\Database\Capsule\Manager as DB;

class EloquentOperations {
    /*
     * Get calls for the dare-database will be handled via
     */

    public static function getBibEntries()
    {
        return BibEntry::with("persons", "categories", "types", "works", "book", "article", "booksection")->get();
    }

    public static function getMsEntries()
    {
        return Document::with("languages", "repository", "document_items")->where("type", "=", "ms")->get();
    }

    public static function getWorkEntries()
    {
        return Work::with("averroes_work", "language")->get();
    }

    public static function getWorkEntryById($id)
    {
        return Work::with("averroes_work", "language")->where("id", "=", $id)->get();
    }

    public static function getAbstractWorkEntries()
    {
        return DB::table("work_averroes")
            ->join('work_category', 'work_averroes.work_category', "=", 'work_category.id')
            ->join('work', 'work_averroes.id', "=", "work.aw_id")
            ->select('work_averroes.id', 'work_averroes.aw_order', 'work_averroes.aw_id', 'work_averroes.aw_title',
                'work_averroes.work_category', 'work_category.category_name')
            ->where("work_category.category_name", "=", "Philosophy of Nature")
            ->get();
    }

    public static function getAbstractWorkByAwid($awid)
    {
        return WorkAverroes::find($awid);
    }

    /**
     * Get Chunks of a document page
     *
     * @param  integer $pageId
     * @return array
     */
    public static function getChunksByPageId($pageId){
        return DocMarker::where("page_id", "=", $pageId)->get()->toArray();
    }

    public static function getDocuments(){
        return Document::with( "languages", "document_items");
    }

    public static function getBibEntryById($id)
    {
        return BibEntry::with("persons", "categories", "types", "works")->where('id','=', $id)->get();
    }

    public static function getDocumentById($id)
    {
        $document=Document::with("languages", "repository", "pages", "document_items", "material")->where("id", "=", $id)->get();
        if (isset($document[0]["document_items"])){
        $document=ManuscriptTools::convertColumns($document);
        }

        return $document;
    }

    public static function getSimpleDocumentById($id)
    {
        $document=Document::with("languages", "repository", "pages" )->where("id", "=", $id)->get();

        return $document;
    }

    public static function getDocumentPageById($id)
    {
        if(DocPage::find($id)){
            $page=DocPage::find($id)->toArray();
        }
        else {
            $page=array();
        }
        return $page;
    }

    public static function getFirstPageByDocumentId($docId, $chunkId, $awid){
        $query=DB::table("doc_page")
            ->join("doc_marker", "doc_marker.page_id", "=", "doc_page.id")
            ->join("work", "doc_marker.work_id", "=", "work.id")
            ->where("doc_page.doc_id", "=", $docId)
            ->where("doc_marker.chunk_no", "=", $chunkId)
            ->where("work.aw_id", "=", $awid)

            ->select("doc_page.*");
        $firstPage=$query->first();
        return $firstPage;
    }

    public static function getBibEntriesCount()
    {
        return BibEntry::count();
    }

    public static function getAllFulltextItems(){
        $query=DB::table("fulltext_item")
            ->join('fulltext_section', 'fulltext_section.id', "=", 'fulltext_item.ft_section_id')
            ->join("fulltext_chapter", "fulltext_chapter.id", "=", "fulltext_item.chapter_id")
            ->join("fulltext_work", "fulltext_work.fulltext_id", "=", "fulltext_section.fulltext_id")
            ->join("work", "work.id", "=", "fulltext_work.work_id")
            ->join("work_averroes", "work_averroes.id", "=", "work.aw_id")
            ->where("work.language", "=", 1)
            ->select(DB::raw('fulltext_item.id as item_id'),"fulltext_item.*", "fulltext_chapter.*", "fulltext_section.*", "work.language", "work.rep_title", "work.translator", "work_averroes.aw_title", "work_averroes.aw_id", "work.author", DB::raw('work.id as work_id'));
        return $fulltexts = $query->get()->toArray();
    }


    public static function getFulltextBaseWithChunks(){
        $query=DB::table("fulltext_base")
            ->select("fulltext_base.*");
        return $query->get()->toArray();

    }

    public static function getFulltextInfoById($id)
    {
        return FulltextBase::where("id", "=", $id)->with("work")->get()->toArray();
    }
    public static function getFulltextChunkById($chunkNo, $fulltextId)
    {
        $query=DB::select("select fulltext_item.*, fulltext_section.* from fulltext_item
                        join fulltext_section on fulltext_section.id=fulltext_item.ft_section_id
                        where fulltext_item.chunk_id= :chunkNo
                        and fulltext_section.fulltext_id= :fulltextId;", ['chunkNo' => $chunkNo, 'fulltextId'=>$fulltextId]);
        return $fulltextChunk = $query;
    }

    public static function getFulltextForExport($id)
    {
        return FulltextBase::where("idno", "=", $id)->with("work", "bib", "chapter")->get()->toArray();
    }



    public static function getFulltextChunksById($fulltextId)
    {

        $query=DB::select("select distinct fulltext_item.chunk_id, fulltext_base.display_title, fulltext_base.language
                from fulltext_item
                join fulltext_section on fulltext_section.id=fulltext_item.ft_section_id
                join fulltext_base on fulltext_section.fulltext_id = fulltext_base.id
                where fulltext_section.fulltext_id= :fulltextId;", ["fulltextId" => $fulltextId]);
        return $fulltextChunk = $query;
    }

    public static function getChunksByWorkId($fulltextId){

        $query=DB::select("select fulltext_item.chunk_id, fs.fulltext_id  from fulltext_item                                                                                                                                join fulltext_section on fulltext_section.id=fulltext_item.ft_section_id
            join fulltext_section as fs on fs.id = fulltext_item.ft_section_id
            where fs.fulltext_id= :fulltextId
            and fulltext_item.item_type = :chunk_start;", ["fulltextId" => $fulltextId, "chunk_start" => "chunk_start"]);
        return $fulltextChunks=$query;

    }

    public static function getFullTextChapterById($id){
        $query=DB::table("fulltext_chapter")
            ->leftJoin("fulltext_item", "fulltext_chapter.id", "=", "fulltext_item.chapter_id")
            ->leftJoin("fulltext_section", "fulltext_item.ft_section_id", "=", "fulltext_section.id")
            ->where("fulltext_chapter.fulltext_id", "=", $id)
            ->where("fulltext_item.item_type", "=", "chapter_start")
            ->select("fulltext_chapter.*", "fulltext_item.*", "fulltext_section.page_label");
        return $fulltextChapters=$query->get()->toArray();


    }

    public static function getAllFulltextChunksById($fulltextId){
        $query=DB::table("fulltext_item")
            ->join('fulltext_section', 'fulltext_section.id', "=", 'fulltext_item.ft_section_id')
            ->where("fulltext_section.fulltext_id", "=", $fulltextId)
            ->select('fulltext_item.id', 'fulltext_item.item_order', 'fulltext_item.item_type', 'fulltext_item.item_text',
                'fulltext_item.alternate_text', 'fulltext_item.chunk_id', 'fulltext_item.chapter_id', 'fulltext_item.app_id',
                'fulltext_item.attributes', "fulltext_section.id", "fulltext_section.fulltext_id", "fulltext_section.section_order", "fulltext_section.page_id");
        return $fulltextChunk = $query->get();

    }

    public static function getFulltextSectionsById($fulltextId){
        return FulltextSection::where("fulltext_id", "=", $fulltextId)->orderBy("section_order")->get()->toArray();
    }

    public static function getFulltextSectionById($sectionId, $fulltextId){
        return FulltextSection::where("id", "=", $sectionId)->with("items")->first()->toArray();
    }

    public static function getFirstSectionByFulltextID($fulltextId){
        return FulltextSection::where("fulltext_id", "=", $fulltextId)->with("items")->orderBy("section_order")->first()->toArray();
    }

    public static function getDocumentsByChunkAndAbstractWork($chunkid, $awid)
    {
        // If we will not have the same document with multiple pages. We should groupBy "document.id" and just select that column.
        // After that, we could get further information by a second query

        $query=DB::select("select document.id, document.type, doc_page.doc_id, work.aw_id, doc_marker.page_id, doc_page.page_number, doc_page.main_folio, document.idno, repository.repository_name, repository.country, repository.settlement, doc_language.language_id from document
            inner join doc_page on document.id = doc_page.doc_id
            inner join doc_marker on doc_page.id = doc_marker.page_id
            join repository on document.repository_id = repository.id
            join doc_language on document.id = doc_language.doc_id
            join work on doc_marker.work_id = work.id
            where work.aw_id=:awid
            and doc_marker.chunk_no = :chunkid;", ['awid' => $awid, 'chunkid'=>$chunkid]);

        return $documents = $query;
    }

    public static function getFulltextsByChunkAndAbstractWork($chunkid, $awid)
    {
        $query=DB::select("select distinct fulltext_base.id, fulltext_base.display_title from fulltext_base
            join fulltext_work on fulltext_base.id = fulltext_work.fulltext_id
            left join fulltext_section on fulltext_base.id = fulltext_section.fulltext_id
            left join fulltext_item on fulltext_section.id = fulltext_item.ft_section_id
            join work on fulltext_work.work_id = work.id
            where work.aw_id= :awid
            and fulltext_item.chunk_id = :chunkid;", ['awid' => $awid, 'chunkid'=>$chunkid] );
        $fulltexts=$query;
        for ($i=0; $i < count($fulltexts); $i++ )
        {
            $fulltexts[$i]->type="fulltext";
            $fulltexts[$i]->page_id=$chunkid;
        }
        return $fulltexts;
    }

    public static function getAbstractWorkByWorkId($id)
    {
        $query=DB::table("work_averroes")
            ->join("work", "work.aw_id", "=", "work_averroes.id")
            ->where("work.id", "=",  $id)
            ->select("work_averroes.id", "work_averroes.aw_id", "work_averroes.aw_title");
        return $abstractWork = $query->get();
    }
}
