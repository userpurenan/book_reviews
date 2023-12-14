import React, { useEffect, useState } from 'react';
import { useCookies } from 'react-cookie';
import { useSelector, useDispatch } from 'react-redux';
import { Link } from 'react-router-dom';
import { signOut } from '../../authSlice';
import './header.scss';
import axios from 'axios';
import { url } from '../../const';

export const Header = () => {
  const auth = useSelector((state) => state.auth.isSignIn);
  const dispatch = useDispatch();
  const [ cookies, , removeCookie] = useCookies();
  const [user, setUsers] = useState('');

  const handleSignOut = () => {
    dispatch(signOut());
    removeCookie('token');
  };

  const headers = {
    authorization: `Bearer ${cookies.token}`,
  };

  useEffect(() => {
    if(auth){ //ログインしていたらユーザー情報を取得する
      axios.get(`${url}/users`, { headers })
      .then((res) => {
         setUsers(res.data); 
      })  
    }
  },[])

  return (
    <header className="header">
      <h1>書籍レビュー</h1>
      {auth ? (
        <div className='userContainer'>
          <Link to={'/'} className="Navigate-button">ホーム画面へ</Link>          
          <Link to={'/new'} className='Navigate-button'>書籍レビュー投稿画面へ</Link>
          <Link to={'/profile'} className="Navigate-button" state={user.name}>ユーザー名変更</Link>
          <Link to={'/login'} onClick={handleSignOut} className="Navigate-button">
            ログアウト
          </Link>
          <br />
          <p className='userName' >
            ユーザー名：{user.name}
            <img src={user.imagePath} alt="ユーザーのアイコン画像" className='userIcon' />
          </p>
        </div>
      ) : (
        <Link to={'/login'} className="Navigate-Login" >ログイン</Link>
      )}
    </header>
  );
};
