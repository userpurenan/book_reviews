import React, { useEffect, useState } from 'react';
import { url } from "../../const";
import { useParams, Link, useNavigate } from "react-router-dom";
import { useCookies } from "react-cookie";
import axios from "axios";
import { Header } from "../header/Header";
import './BookReviewDetail.scss';
import Loading from "../Loding";


export const BookReviewDetail = () => {
    const { BookId } = useParams(); //クエリパラメータを取得するには[]ではなく{}で囲わなければならない
    const [cookies] = useCookies();
    const navigate = useNavigate();
    const [isLoading, setIsLoading ] = useState(false);
    const [bookData, setBookData] = useState('');
    const [errorMessage, setErrorMessage] = useState('');

    const headers = {
        authorization: `Bearer ${cookies.token}`,
    };

    useEffect(() => {
        setIsLoading(true);

        if(! cookies.token) navigate('/login');

        axios.get(`${url}/books/${BookId}`, { headers })
        .then((res) => {
            const bookData = res.data;
            setBookData(bookData); //書籍の情報を一個にまとめた
        })
        .catch((err) => {
            setErrorMessage(`エラー発生 ${err}`);
        })
        .finally(() => { //「finally」は最後に必ず実行される処理群
            setIsLoading(false);
        });

        axios.post(`${url}/logs`, { selectBookId: BookId}, { headers })
        .catch((err)=> {
            setErrorMessage(`ログの送信に失敗しました${err}`)
        })
    }, [BookId])

    return (
        <div>
            <Header />
            <h1>書籍の詳細</h1>
            <h2 className="error-massage">{errorMessage}</h2>
            {isLoading ? <Loading />:
            
            <div className="bookDetail">
                <p className="bookDetail__title">タイトル: {bookData.title}</p>
                <p className="bookDetail__url">URL: <a href={bookData.url}>{bookData.url}</a></p>
                <p className="bookDetail__reviewer">レビュワー: {bookData.reviewer}</p>
                <p className="bookDetail__detail">書籍の詳細情報: {bookData.detail}</p>
                <p className="bookDetail__review">レビュー: {bookData.review}</p>
                {bookData.isMine ? <Link to={`/edit/${BookId}`}>書籍編集画面へ</Link> : <></>}
            </div>}
        </div>
    )
}
