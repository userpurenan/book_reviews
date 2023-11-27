import React, { useState, useEffect } from "react";
import { Header } from "../header/Header";
import axios from "axios";
import { useCookies } from "react-cookie";
import { url } from "../../const";
import { AiOutlineSearch } from "react-icons/ai";
import { IconContext } from 'react-icons'
import './Home.scss';
import { Link } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import { beforePagenation, nextPagenation } from '../../pagenationSlice';
import { useLocation } from "react-router-dom";


export const Home = () => {
    const [Books, setBooks] = useState([]);
	const search = useLocation().search;
	const query = new URLSearchParams(search);
	const title = query.get('title_keyword')
    const [cookies] = useCookies();
    const auth = useSelector((state) => state.auth.isSignIn);
    const currentPage = useSelector((state) => state.pagenation.currentPage); //初期値は「０」
    const dispatch = useDispatch();

    const headers = {
        authorization: `Bearer ${cookies.token}`,
    };

    useEffect(() => {
	  if(title){
		axios.get(`${url}/public/books?title_keyword=${title}`)
			.then((res)=> {
				setBooks(res.data);
			});

		return
	  }

      if(auth){   //ログインしていたら認証情報が必要なAPIから情報を取得する
        axios.get(`${url}/books`, { headers })
          .then((res) => {
            setBooks(res.data); 
          })

        return
      }

        axios.get(`${url}/public/books`)
        .then((res) => {
          setBooks(res.data); 
        })
      },[]);

        const handlePagenation = async (offset, e) => {
          const res = await axios.get(`${url}/public/books`,{
            params: {
                offset: offset // ここにクエリパラメータを指定する。
            }
          });
          setBooks([]);
          setBooks(res.data);
          e.target.id === 'next' ? dispatch(nextPagenation()) : dispatch(beforePagenation());
        };

		const handleSearch = async () => {
			const res = await axios.get(`${url}/search/books`);
			setBooks([]);
			setBooks(res.data);
		};

   return(
        <div className="page">
            <Header />
            <h1>書籍レビュー一覧</h1>
            <div className="float_page">
              <form onSubmit={handleSearch} >
                  <input className="search" type="text" name="title_keyword" defaultValue={title}  placeholder="書籍のタイトルを入力" />
                  <IconContext.Provider value={{ size: '15px' }}>
                    <button type="submit" className="button"><AiOutlineSearch /></button>
                  </IconContext.Provider>
              </form>
              <ul className="Book">
              {Books.map((BookList, key) => (
                <li key={key} className="Book__list" value={BookList.id}>
                  
                  <Link to={`/detail/${BookList.id}`} className="Book__list--link" >{BookList.title}</Link>
                </li>
              ))}
              </ul>
              <Pagination 
                  currentPage={currentPage} 
                  Pagenation={handlePagenation}
                  Books={Books}
              />
            </div>
        </div>
    )
}

const Pagination = ({ currentPage, Pagenation, Books }) => {
    return (
      <div className="pagenation">
          {currentPage !== 0 ?
           <button
            id="before"
            onClick={(e)=> {Pagenation((currentPage - 1) * 10, e)}}
            className="pagenation__button"
          >
            前のページへ
          </button> : <></>}
          <input type="text" className="pagenation__currentPage" value={currentPage + 1} readOnly />
          {Books.length === 10 ?
          <button
            id="next"
            onClick={(e) => {Pagenation((currentPage + 1) * 10, e)}}
            className="pagenation__button"
          >
            次のページへ
          </button> : <></>}
      </div>
    );
};  

export default Home;
